const crypto = require('crypto');
require('dotenv').config();

// Decode and validate the base64-encoded key from the .env file
const key = Buffer.from(process.env.KEY, 'base64');
console.log('Key length:', key.length); // Output: 32
class EncryptModel {
  /**
   * Encrypts data using AES-256-CBC.
   * @param {string} data - The data to encrypt.
   * @returns {string} - The encrypted string.
   */
  encryptOA(data) {
    const iv = crypto.randomBytes(16); // Generate a random initialization vector
    const cipher = crypto.createCipheriv('aes-256-cbc', key, iv);

    let encrypted = cipher.update(data, 'utf8', 'base64');
    encrypted += cipher.final('base64');

    // Concatenate encrypted data with IV and encode in base64
    const encryptedIvCombined = `${encrypted}::${iv.toString('base64')}`;
    return Buffer.from(encryptedIvCombined).toString('base64').slice(0, -1);
  }

  /**
   * Decrypts data encrypted with encryptOA.
   * @param {string} data - The encrypted data to decrypt.
   * @returns {string|null} - The decrypted string or null if decryption fails.
   */
  decryptOA(data) {
    try {
      // Decode the base64 data and add the '=' back to complete the padding
      const decodedData = Buffer.from(`${data}=`, 'base64').toString('utf8');
      const [encryptedData, iv] = decodedData.split('::');

      if (!iv || Buffer.from(iv, 'base64').length !== 16) {
        throw new Error('Invalid IV length');
      }

      const decipher = crypto.createDecipheriv(
        'aes-256-cbc',
        key,
        Buffer.from(iv, 'base64')
      );

      let decrypted = decipher.update(encryptedData, 'base64', 'utf8');
      decrypted += decipher.final('utf8');

      return decrypted;
    } catch (error) {
      console.error('Decryption error:', error.message);
      return null; // Return null if decryption fails
    }
  }
}

module.exports = new EncryptModel();
