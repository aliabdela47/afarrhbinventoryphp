/**
 * Ethiopian Calendar Conversion Utilities
 * AfarRHB Inventory Management System
 */

/**
 * Convert Gregorian date to Ethiopian date
 */
function gregorianToEthiopian(gregYear, gregMonth, gregDay) {
    const jdn = gregorianToJDN(gregYear, gregMonth, gregDay);
    return jdnToEthiopian(jdn);
}

/**
 * Convert Ethiopian date to Gregorian date
 */
function ethiopianToGregorian(ethYear, ethMonth, ethDay) {
    const jdn = ethiopianToJDN(ethYear, ethMonth, ethDay);
    return jdnToGregorian(jdn);
}

/**
 * Convert Gregorian to Julian Day Number
 */
function gregorianToJDN(year, month, day) {
    const a = Math.floor((14 - month) / 12);
    const y = year + 4800 - a;
    const m = month + 12 * a - 3;
    
    return day + Math.floor((153 * m + 2) / 5) + 365 * y + 
           Math.floor(y / 4) - Math.floor(y / 100) + 
           Math.floor(y / 400) - 32045;
}

/**
 * Convert Ethiopian to Julian Day Number
 */
function ethiopianToJDN(year, month, day) {
    return (1723856 + 365) + 
           365 * (year - 1) + 
           Math.floor(year / 4) + 
           30 * month + 
           day - 31;
}

/**
 * Convert Julian Day Number to Gregorian
 */
function jdnToGregorian(jdn) {
    const a = jdn + 32044;
    const b = Math.floor((4 * a + 3) / 146097);
    const c = a - Math.floor((146097 * b) / 4);
    const d = Math.floor((4 * c + 3) / 1461);
    const e = c - Math.floor((1461 * d) / 4);
    const m = Math.floor((5 * e + 2) / 153);
    
    const day = e - Math.floor((153 * m + 2) / 5) + 1;
    const month = m + 3 - 12 * Math.floor(m / 10);
    const year = 100 * b + d - 4800 + Math.floor(m / 10);
    
    return { year, month, day };
}

/**
 * Convert Julian Day Number to Ethiopian
 */
function jdnToEthiopian(jdn) {
    const r = (jdn - 1723856) % 1461;
    const n = (r % 365) + 365 * Math.floor(r / 1460);
    
    const year = 4 * Math.floor((jdn - 1723856) / 1461) + 
                 Math.floor(r / 365) - Math.floor(r / 1460);
    const month = Math.floor(n / 30) + 1;
    const day = (n % 30) + 1;
    
    return { year, month, day };
}

/**
 * Get Ethiopian month name
 */
function getEthiopianMonthName(month, lang = 'en') {
    const months = {
        en: [
            'Meskerem', 'Tikimt', 'Hidar', 'Tahsas',
            'Tir', 'Yekatit', 'Megabit', 'Miazia',
            'Ginbot', 'Sene', 'Hamle', 'Nehase', 'Pagume'
        ],
        am: [
            'መስከረም', 'ጥቅምት', 'ኅዳር', 'ታኅሣሥ',
            'ጥር', 'የካቲት', 'መጋቢት', 'ሚያዝያ',
            'ግንቦት', 'ሰኔ', 'ሐምሌ', 'ነሐሴ', 'ጳጉሜ'
        ]
    };
    
    return months[lang][month - 1] || month;
}

/**
 * Format Ethiopian date
 */
function formatEthiopianDate(ethDate, lang = 'en') {
    const monthName = getEthiopianMonthName(ethDate.month, lang);
    return `${ethDate.day} ${monthName} ${ethDate.year}`;
}

/**
 * Format Gregorian date
 */
function formatGregorianDate(gregDate) {
    const months = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];
    return `${gregDate.day} ${months[gregDate.month - 1]} ${gregDate.year}`;
}

/**
 * Parse date string (YYYY-MM-DD)
 */
function parseDate(dateString) {
    const parts = dateString.split('-');
    return {
        year: parseInt(parts[0]),
        month: parseInt(parts[1]),
        day: parseInt(parts[2])
    };
}

/**
 * Format date object to string (YYYY-MM-DD)
 */
function formatDateString(dateObj) {
    const year = dateObj.year.toString().padStart(4, '0');
    const month = dateObj.month.toString().padStart(2, '0');
    const day = dateObj.day.toString().padStart(2, '0');
    return `${year}-${month}-${day}`;
}

/**
 * Display date based on current calendar setting
 */
function displayDate(gregorianDateString, calendarType = 'gregorian', lang = 'en') {
    const gregDate = parseDate(gregorianDateString);
    
    if (calendarType === 'ethiopian') {
        const ethDate = gregorianToEthiopian(gregDate.year, gregDate.month, gregDate.day);
        return formatEthiopianDate(ethDate, lang);
    }
    
    return formatGregorianDate(gregDate);
}
