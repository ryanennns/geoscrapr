export function usePlayerUtils() {
    const getFlagEmoji = (countryCode) => {
        if (!countryCode || countryCode.length !== 2) {
            return '🏴';
        }

        try {
            return String.fromCodePoint(
                ...countryCode
                    .toUpperCase()
                    .split('')
                    .map(char => 127397 + char.charCodeAt())
            );
        } catch {
            return '🏴';
        }
    }

    const generateProfileUrl = (id) => {
        return `https://www.geoguessr.com/user/${id}`;
    }

    return {
        getFlagEmoji,
        generateProfileUrl,
    }
}
