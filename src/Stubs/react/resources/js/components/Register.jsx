import React, { useState } from 'react';
import { api } from '../http';
import AuthLayout from './AuthLayout';
import Input from './Input';
import Button from './Button';

export default function Register({ registerUrl, loginUrl }) {
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');
    const [errors, setErrors] = useState({});
    const [message, setMessage] = useState('');
    const [loading, setLoading] = useState(false);

    async function handleSubmit(e) {
        e.preventDefault();
        setErrors({});
        setMessage('');
        setLoading(true);

        try {
            const data = await api(registerUrl, {
                method: 'POST',
                body: {
                    name,
                    email,
                    password,
                    password_confirmation: passwordConfirmation,
                },
            });
            window.location.href = data.redirect || '/dashboard';
        } catch (err) {
            if (err.status === 422 && err.data?.errors) {
                const next = {};
                Object.keys(err.data.errors).forEach((k) => {
                    next[k] = Array.isArray(err.data.errors[k])
                        ? err.data.errors[k][0]
                        : err.data.errors[k];
                });
                setErrors(next);
            } else {
                setMessage(err.data?.message || err.message || 'Registration failed.');
            }
        } finally {
            setLoading(false);
        }
    }

    return (
        <AuthLayout
            title="Create account"
            subtitle="Fill in the form to get started"
            footer={
                <a href={loginUrl || '/login'} className="link-sub">
                    Already have an account? Sign in
                </a>
            }
        >
            {message && (
                <div className="alert alert-error" style={{ marginBottom: 16 }}>
                    <i className="bx bx-error-circle" />
                    <span>{message}</span>
                </div>
            )}
            <form onSubmit={handleSubmit} noValidate>
                <Input
                    name="name"
                    label="Name"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                    error={errors.name}
                    placeholder="Your name"
                    autoComplete="name"
                    required
                />
                <Input
                    name="email"
                    type="email"
                    label="Email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    error={errors.email}
                    placeholder="you@example.com"
                    autoComplete="email"
                    required
                />
                <Input
                    name="password"
                    type="password"
                    label="Password"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    error={errors.password}
                    placeholder="Min. 8 characters"
                    autoComplete="new-password"
                    required
                />
                <Input
                    name="password_confirmation"
                    type="password"
                    label="Confirm password"
                    value={passwordConfirmation}
                    onChange={(e) => setPasswordConfirmation(e.target.value)}
                    error={errors.password_confirmation}
                    placeholder="Repeat password"
                    autoComplete="new-password"
                    required
                />
                <Button full loading={loading}>Create account</Button>
            </form>
        </AuthLayout>
    );
}