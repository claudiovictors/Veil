import React from 'react';

export default function Button({ children, type = 'submit', loading = false, full = false, onClick }) {
    return (
        <button
            type={type}
            className={`btn${full ? ' btn-full' : ''}`}
            disabled={loading}
            onClick={onClick}
        >
            {loading ? 'Please wait…' : children}
        </button>
    );
}