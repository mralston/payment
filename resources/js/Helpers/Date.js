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

export function fromNow(date) {
    return moment(date).fromNow();
}

export function iso8601(date) {
    return moment(date).toISOString();
}
