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

    return {
        getId,
        setId,
        clearId,
    };
}
