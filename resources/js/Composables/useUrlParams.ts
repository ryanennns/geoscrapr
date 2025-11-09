const key = "id";

export function useUrlParams() {
    const setId = (value: string): void => {
        const url = new URL(window.location.href);
        if (url.searchParams.has(key)) {
            url.searchParams.delete(key);
        }
        url.searchParams.append(key, value);
        history.replaceState(null, "", url.toString());
    };

    const clearId = () => {
        const url = new URL(window.location.href);
        if (url.searchParams.has(key)) {
            url.searchParams.delete(key);
        }
        history.replaceState(null, "", url.toString());
    };

    const getId = () => {
        const url = new URL(window.location.href);

        return url.searchParams.get(key);
    };

    const get = (key: string): unknown => {
        const url = new URL(window.location.href);
        console.log(window.location.href);
        return url.searchParams.get(key);
    };

    const set = (key: string, value: string): void => {
        const url = new URL(window.location.href);
        if (url.searchParams.has(key)) {
            url.searchParams.delete(key);
        }
        url.searchParams.append(key, value);
        history.replaceState(null, "", url.toString());
    };

    const clear = (key: string): void => {
        const url = new URL(window.location.href);
        if (url.searchParams.has(key)) {
            url.searchParams.delete(key);
        }
        history.replaceState(null, "", url.toString());
    };

    return {
        getId,
        setId,
        clearId,
        get,
        set,
        clear,
    };
}
