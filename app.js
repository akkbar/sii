require('dotenv').config();
const express = require('express')
const session = require('express-session')
const path = require('path')
const cors = require('cors')
const authRoutes = require('./routes/auth')
const userRoutes = require('./routes/user')
const siiRoutes = require('./routes/sii')
const http = require('http')
const favicon = require('serve-favicon')
const MongoDBStore = require('connect-mongodb-session')(session)
const mongoose = require('./models/mongoDb')

const app = express()
app.use(favicon(path.join(__dirname, 'public', 'favicon.ico')))

const store = new MongoDBStore({
  uri: process.env.MONGO_URI,
  collection: 'sessions',
});

store.on('error', (error) => {
  console.error('Session store error:', error);
});

app.use(
  session({
    secret: process.env.OPENAIOT_KEY, 
    resave: false, 
    saveUninitialized: false,
    store: store,
    cookie: {
      maxAge: 1000 * 60 * 60 * 24 * 30, // 30 day in milliseconds
      secure: process.env.NODE_ENV === 'production',
    },
  })
);


const server = http.createServer(app)


app.use(express.urlencoded({ extended: true }))
app.use(express.static(path.join(__dirname, 'public')))
app.set('views', path.join(__dirname, 'views'))
app.use('/libs', express.static('node_modules'))
app.locals.publicPath = path.join(__dirname, 'public')
app.locals.headerPath = path.join(__dirname, 'views', 'includes', 'header.ejs')
app.locals.footerPath = path.join(__dirname, 'views', 'includes', 'footer.ejs')
app.set('view engine', 'ejs')
app.use(express.json())
app.use(cors())


app.use('/', authRoutes);
app.use('/users', userRoutes);
app.use('/sii', siiRoutes);

app.get('/', (req, res) => {
    if (req.session.user) {
      res.redirect('/dashboard')
    } else {
      res.redirect('/login')
    }
})

app.get('/dashboard', (req, res) => {
  if (req.session.user) {
    res.redirect('/sii/dashboard')
  } else {
    res.redirect('/login')
  }
})

app.use((req, res) => {
    res.status(404).render('404', { message: 'Page not found' })
})

app.use((err, req, res, next) => {
  console.error('Unhandled error:', err);
  res.status(500).render('500', { message: 'Internal Server Error' });
})

server.listen(3001, '::', () => {
    console.log('Server running on http://localhost/')
})

process.on('SIGINT', async () => {
  console.log('Shutting down gracefully...');
  try {
    await mongoose.disconnect();
    server.close(() => {
      console.log('Server closed');
      process.exit(0);
    });
  } catch (error) {
    console.error('Error during shutdown:', error);
    process.exit(1);
  }
})