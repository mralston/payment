import pako from "pako";

export function decompress(encodedPayload)
{
    try {
        // Decode the base64 string
        const binaryString = atob(encodedPayload);
        const len = binaryString.length;
        const bytes = new Uint8Array(len);
        for (let i = 0; i < len; i++) {
            bytes[i] = binaryString.charCodeAt(i);
        }

        // Decompress the data using pako
        const decompressedData = pako.inflate(bytes, {to: 'string'});

        // Parse the JSON string back into a JavaScript object
        return JSON.parse(decompressedData);
    } catch (e) {
        console.error('Error decompressing payload:', e);
        console.log(encodedPayload);
        return null;
    }
}
