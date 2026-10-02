import React from 'react';

export default function Input({
    id,
    name,
    type = 'text',
    label,
    value,
    onChange,
    error,
    placeholder,
    autoComplete,
    required = false,
}) {
    return (
        <div className="form-group">
            {label && (
                <label className="form-label" htmlFor={id || name}>
                    {label} {required && <sup>*</sup>}
                </label>
            )}
            <input
                id={id || name}
                name={name}
                type={type}
                className={`form-input${error ? ' form-input--error' : ''}`}
                value={value}
                onChange={onChange}
                placeholder={placeholder}
                autoComplete={autoComplete}
                required={required}
            />
            {error && <span className="form-error">{error}</span>}
        </div>
    );
}