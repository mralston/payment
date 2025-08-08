import moment from "moment";

export function formatDate (date, format) {
    if (date == null) {
        return moment().format(format);
    }

    return moment(date).format(format);
}

export function subDate (date, amount, unit) {
    return moment(date).subtract(amount, unit).format('YYYY-MM-DD');
}

export function addDate (date, amount, unit) {
    return moment(date).add(amount, unit).format('YYYY-MM-DD');
}

export function diffInDays(date1, date2) {
    if (date1 == null) {
        date1 = moment().format('YYYY-MM-DD');
    } else {
        date1 = moment(date1).format('YYYY-MM-DD');
    }

    if (date2 == null) {
        date2 = moment().format('YYYY-MM-DD');
    } else {
        date2 = moment(date2).format('YYYY-MM-DD');
    }

    return moment(date1).diff(moment(date2)) / 1000 / 60 / 60 / 24;
}

export function diffInMonths(date1, date2) {
    return moment(date1).diff(moment(date2), 'months');
}

export function fromNow(date, withoutSuffix) {
    return moment(date).fromNow(withoutSuffix);
}

export function iso8601(date) {
    return moment(date).toISOString();
}

export function monthsYears(total_months) {
    let years = Math.floor(total_months / 12);
    let months = Math.round(total_months % 12);

    // Rounding to 0dp sometimes leaves us with what appears to be 12 months
    // Round the years up if that happens
    if (months === 12) {
        years++;
        months = 0;
    }

    let output = '';

    if (years > 0) {
        output = years + ' year' + (years === 1 ? '' : 's');
    }

    if (years > 0 && months > 0) {
        output += ', ';
    }

    if (months > 0) {
        output += months + ' month' + (months === 1 ? '' : 's');
    }

    return output;
}
