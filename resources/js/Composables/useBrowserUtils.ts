import { onMounted, ref } from "vue";

export function useBrowserUtils() {
    const isMobile = ref(false);

    onMounted(() => {
        const mql = window.matchMedia("(max-width: 767px)");
        isMobile.value = mql.matches;
        mql.addEventListener("change", (e) => {
            isMobile.value = e.matches;
        });
    });

    return {
        isMobile,
    };
}
