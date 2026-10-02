/**
 * JSON fetch helper with CSRF and credentials.
 * On non-OK responses, throws Error with .status and .data (parsed JSON body).
 */
export async function api(url, { method = 'GET', body } = {}) {
    const headers = {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    };

    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) {
        headers['X-CSRF-TOKEN'] = meta.getAttribute('content');
    }

    const options = {
        method,
        headers,
        credentials: 'same-origin',
    };

    if (body !== undefined) {
        headers['Content-Type'] = 'application/json';
        options.body = JSON.stringify(body);
    }

    const res = await fetch(url, options);

    let data = null;
    const text = await res.text();
    if (text) {
        try {
            data = JSON.parse(text);
        } catch {
            data = null;
        }
    }

    if (!res.ok) {
        const err = new Error(data?.message || res.statusText || 'Request failed');
        err.status = res.status;
        err.data = data;
        throw err;
    }

    return data;
}