'use strict';
window.onload = function() {
    document.addEventListener('keydown', function (e) {
        if (e.code === 'Enter') e.preventDefault();
    });
};