const cncDb = require('../models/cncDb');
const moment = require('moment')
const logError = require('../middlewares/errorlogger')

async function getCurrentShift() {
    try {
        // 1. Fetch the active shift data where isactive = 1
        const activeShiftRow = await cncDb('production_shift')
            .select('*')
            .where('isactive', 1)
            .first(); // Fetch the first matching row

        if (!activeShiftRow) {
            console.log('No active shift configuration found.');
            return;
        }

        // 2. Get current time
        const now = moment(); // Current datetime
        const currentTime = now.format('HH:mm:ss');

        let currentShift = null;

        // 3. Determine the current shift
        const shifts = [
            {
                shift: 1,
                start: activeShiftRow.shift1_start,
                end: activeShiftRow.shift1_end
            },
            {
                shift: 2,
                start: activeShiftRow.shift2_start,
                end: activeShiftRow.shift2_end
            },
            {
                shift: 3,
                start: activeShiftRow.shift3_start,
                end: activeShiftRow.shift3_end
            }
        ];

        // Loop through the shifts
        for (const shift of shifts) {
            const shiftStart = moment(shift.start, 'HH:mm:ss');
            const shiftEnd = moment(shift.end, 'HH:mm:ss');

            let startDateTime, endDateTime;

            if (shiftEnd.isBefore(shiftStart)) {
                // Shift crosses midnight
                if (now.isAfter(shiftStart) || now.isBefore(shiftEnd)) {
                    startDateTime = moment(now).set({
                        hour: shiftStart.hour(),
                        minute: shiftStart.minute(),
                        second: shiftStart.second()
                    });

                    endDateTime = moment(startDateTime).add(1, 'day').set({
                        hour: shiftEnd.hour(),
                        minute: shiftEnd.minute(),
                        second: shiftEnd.second()
                    });

                    currentShift = {
                        shift: shift.shift,
                        start: startDateTime.format('YYYY-MM-DD HH:mm:ss'),
                        end: endDateTime.format('YYYY-MM-DD HH:mm:ss')
                    };
                    break;
                }
            } else {
                // Regular shift within the same day
                if (now.isBetween(shiftStart, shiftEnd)) {
                    startDateTime = moment(now).set({
                        hour: shiftStart.hour(),
                        minute: shiftStart.minute(),
                        second: shiftStart.second()
                    });

                    endDateTime = moment(startDateTime).set({
                        hour: shiftEnd.hour(),
                        minute: shiftEnd.minute(),
                        second: shiftEnd.second()
                    });

                    currentShift = {
                        shift: shift.shift,
                        start: startDateTime.format('YYYY-MM-DD HH:mm:ss'),
                        end: endDateTime.format('YYYY-MM-DD HH:mm:ss')
                    };
                    break;
                }
            }
        }

        // 4. Output the current shift details
        if (currentShift) {
            // console.log('Current Shift:', currentShift);
            return currentShift
        } else {
            console.log('No active shift found for the current time.');
        }

    } catch (error) {
        await logError('error', error.message, error.stack, 'getCurrentShift');
        console.error('Error:', error.message);
    } 
}
module.exports = getCurrentShift;