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

export function cleanUrl(url) {
    // Remove http://, https://, and www. from the beginning
    let cleanedUrl = url.replace(/^(https?:\/\/)?(www\.)?/, '');

    // Remove the trailing / from the end
    cleanedUrl = cleanedUrl.replace(/\/$/, '');

    return cleanedUrl;
}
