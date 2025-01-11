const mongoose = require('../models/mongoDb')
const ErrorLog = require('../models/errorlog');

async function logError(level, message, stack, additionalInfo) {
  const errorLog = new ErrorLog({ level, message, stack, additionalInfo });
  await errorLog.save();
}

module.exports = logError;