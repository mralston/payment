export function titleCase(str) {
    if (!str) {
        return '';
    }

    let chars = str.toLowerCase().split(' ');
    for (var i = 0; i < chars.length; i++) {
        chars[i] = chars[i].charAt(0).toUpperCase() + chars[i].slice(1);
    }
    return chars.join(' ');
}
