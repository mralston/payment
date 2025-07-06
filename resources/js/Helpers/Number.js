export function makeNumeric (value) {
    value = parseFloat(value);
    if (isNaN(value)) {
        value = 0;
    }
    return value;
}

export function round(value, precision) {
    return +value.toFixed(precision);
}

export function toMax2DP (value) {
    if (isNaN(parseFloat(value))) {
        return value;
    }

    let formatter = new Intl.NumberFormat('en-GB', {
        maximumFractionDigits: 2,
    });

    return formatter.format(value);
}