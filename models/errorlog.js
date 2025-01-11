const mongoose = require('mongoose');

const errorLogSchema = new mongoose.Schema({
  timestamp: { type: Date, default: Date.now },
  level: { type: String, required: true },
  message: { type: String, required: true },
  stack: { type: String },
  additionalInfo: { type: mongoose.Schema.Types.Mixed },
  resolved: { type: Boolean, default: false },
  synced: { type: Boolean, default: false },
});

errorLogSchema.index({ timestamp: 1 }, { expireAfterSeconds: 60 * 60 * 24 * 30 }); // 30 days

const ErrorLog = mongoose.model('ErrorLog', errorLogSchema);

module.exports = ErrorLog;