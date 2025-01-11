const mongoose = require('mongoose');

mongoose.connect(process.env.MONGO_URI, {
    maxPoolSize: 10, // Limit the connection pool size
})
.then(() => console.log('Connected to MongoDB'))
.catch(err => console.error('MongoDB connection error:', err));

module.exports = mongoose;