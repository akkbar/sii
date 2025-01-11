const Joi = require('joi');
const validator = require('validator');
class cncValidator {
    static joiMachineData(data) {
        const machineSchema = Joi.object({
            mc_name: Joi.string().min(5).max(20).required(),
            mc_type: Joi.number().integer().min(1).max(255).required(),
            mc_group: Joi.number().integer().positive().allow(null).optional(),
            ip: Joi.string().ip({ version: ['ipv4'], cidr: 'forbidden' }).required() 
        });
        return machineSchema.validate(data, { stripUnknown: true });
    }
    static sanitizeMachine(data) {
        return {
            ...data,
            mc_name: validator.escape(data.mc_name),
            mc_type: parseInt(data.mc_type, 10),
            mc_group: data.mc_group ? parseInt(data.mc_group, 10) : null,
            ip: data.ip.trim()
        };
    }
    static joiMachineGroup(data) {
        const machineGroupSchema = Joi.object({
            group_name: Joi.string().min(3).max(50).required()
        })
        return machineGroupSchema.validate(data, { stripUnknown: true });
    }
    static sanitizeMachineGroup(data) {
        return {
            ...data,
            group_name: validator.escape(data.group_name)
        };
    }
    static joiMachineDisplay(data) {
        const machineDisplaySchema = Joi.object({
            display_name: Joi.string().min(3).max(50).required()
        })
        return machineDisplaySchema.validate(data, { stripUnknown: true });
    }
    static sanitizeMachineDisplay(data) {
        return {
            ...data,
            display_name: validator.escape(data.display_name)
        };
    }
    static joiMachineLayout(data) {
        const machineLayoutSchema = Joi.object({
            col_id: Joi.string().min(3).max(50).regex(/^[a-zA-Z][a-zA-Z0-9-_]*$/).required()
        })
        return machineLayoutSchema.validate(data, { stripUnknown: true });
    }
    static sanitizeMachineLayout(data) {
        return {
            ...data,
            col_id: validator.escape(data.col_id)
        };
    }
    static joiShiftData(data) {
        const shiftSchema = Joi.object({
            shift1_start: Joi.string()
                .pattern(/^([01]\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/) // Matches HH:mm and HH:mm:ss
                .required()
                .messages({ 'string.pattern.base': 'Shift 1 Start must be in HH:mm or HH:mm:ss format' }),
            shift1_end: Joi.string()
                .pattern(/^([01]\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/) // Matches HH:mm and HH:mm:ss
                .required()
                .messages({ 'string.pattern.base': 'Shift 1 Start must be in HH:mm or HH:mm:ss format' }),
            shift2_start: Joi.string()
                .pattern(/^([01]\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/) // Matches HH:mm and HH:mm:ss
                .required()
                .messages({ 'string.pattern.base': 'Shift 1 Start must be in HH:mm or HH:mm:ss format' }),
            shift2_end: Joi.string()
                .pattern(/^([01]\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/) // Matches HH:mm and HH:mm:ss
                .required()
                .messages({ 'string.pattern.base': 'Shift 1 Start must be in HH:mm or HH:mm:ss format' }),
            shift3_start: Joi.string()
                .pattern(/^([01]\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/) // Matches HH:mm and HH:mm:ss
                .required()
                .messages({ 'string.pattern.base': 'Shift 1 Start must be in HH:mm or HH:mm:ss format' }),
            shift3_end: Joi.string()
                .pattern(/^([01]\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/) // Matches HH:mm and HH:mm:ss
                .required()
                .messages({ 'string.pattern.base': 'Shift 1 Start must be in HH:mm or HH:mm:ss format' }),
            isactive: Joi.number()
                .integer()
                .valid(0, 1)
                .default(0),
        });
        return shiftSchema.validate(data, { stripUnknown: true });
    }
    static sanitizeShiftData(data) {
        return {
            ...data,
            shift1_start: validator.escape(data.shift1_start).trim(),
            shift1_end: validator.escape(data.shift1_end).trim(),
            shift2_start: validator.escape(data.shift2_start).trim(),
            shift2_end: validator.escape(data.shift2_end).trim(),
            shift3_start: validator.escape(data.shift3_start).trim(),
            shift3_end: validator.escape(data.shift3_end).trim(),
            isactive: parseInt(data.isactive, 10), // Ensure isactive is an integer
        };
    }
    static joiProductData(data) {
        const productSchema = Joi.object({
            product_name: Joi.string().min(5).max(50).required(),
            // ideal_ct: Joi.number().positive().allow(null).optional(),
            ideal_mode: Joi.string().valid('Auto', 'Manual').required(), 
        });
        return productSchema.validate(data, { stripUnknown: true });
    }
    static sanitizeProductData(data) {
        return {
            ...data,
            product_name: validator.escape(data.product_name),
        };
    }
    static joiBaseline(data) {
        const baselineSchema = Joi.object({
            utilization: Joi.number().positive().precision(2).allow(null).optional(),
            oee: Joi.number().positive().precision(2).allow(null).optional(),
            ar: Joi.number().positive().precision(2).allow(null).optional(),
            pr: Joi.number().positive().precision(2).allow(null).optional(),
            qr: Joi.number().positive().precision(2).allow(null).optional(),
            isactive: Joi.number()
                .integer()
                .valid(0, 1)
                .default(0),
        });
        return baselineSchema.validate(data, { stripUnknown: true });
    }
    static sanitizeBaseline(data) {
        return {
            ...data,
            utilization: typeof data.utilization === 'number' ? parseFloat(data.utilization.toFixed(2)) : null,
            oee: typeof data.oee === 'number' ? parseFloat(data.oee.toFixed(2)) : null,
            ar: typeof data.ar === 'number' ? parseFloat(data.ar.toFixed(2)) : null,
            pr: typeof data.pr === 'number' ? parseFloat(data.pr.toFixed(2)) : null,
            qr: typeof data.qr === 'number' ? parseFloat(data.qr.toFixed(2)) : null,
            isactive: parseInt(data.isactive, 10), // Ensure isactive is an integer
        };
    }
    static joiRejectCategory(data) {
        const schema = Joi.object({
            category_name: Joi.string().min(5).max(150).required(),
        });
        return schema.validate(data, { stripUnknown: true });
    }
    static sanitizeRejectCategory(data) {
        return {
            ...data,
            category_name: validator.escape(data.category_name),
        };
    }
    static joiDowntimeCategory(data) {
        const schema = Joi.object({
            category_name: Joi.string().min(5).max(150).required(),
        });
        return schema.validate(data, { stripUnknown: true });
    }
    static sanitizeDowntimeCategory(data) {
        return {
            ...data,
            category_name: validator.escape(data.category_name),
        };
    }
}

module.exports = cncValidator
