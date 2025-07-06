import { makeNumeric } from "./Number.js";

export function formatCurrency (amount, dp) {
    if (isNaN(parseFloat(amount))) {
        return null;
    }

    let gbp = Intl.NumberFormat("en-GB", {
        style: "currency",
        currency: "GBP",
        minimumFractionDigits: dp ?? 2,
        maximumFractionDigits: dp ?? 2,
    });

    return gbp.format(amount);
}

export function roundCurrency (amount) {
    return Math.round((makeNumeric(amount) + Number.EPSILON) * 100) / 100;
}

export function toPounds (value) {
    if (isNaN(parseFloat(value))) {
        return value;
    }

    let formatter = new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

    return formatter.format(value);
}

export function toShortPounds (value) {
    if (isNaN(parseFloat(value))) {
        return value;
    }

    let formatter = new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        //minimumFractionDigits: 2
        maximumFractionDigits: 2,
    });

    return formatter.format(value);
}

export function toShortCurrency (value) {
    if (isNaN(parseFloat(value))) {
        return value;
    }

    let formatter = new Intl.NumberFormat('en-GB', {
        //style: 'currency',
        //currency: 'GBP',
        //minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

    return formatter.format(value);
}
