/**
 * Embellish Fields External Module
 * Adds field metadata display to data entry forms
 */

function AddTag(field, actionTag) {
    const ele = document.getElementById('label-' + field);
    if (ele) {
        const info = document.createElement('div');
        info.className = 'embellish-field-info';
        info.innerHTML = '<small>' + escapeHtml(actionTag) + '</small>';
        ele.insertAdjacentElement('afterend', info);
    }
}

/**
 * Escape HTML entities to prevent XSS
 * @param {string} text - Text to escape
 * @returns {string} Escaped text
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
