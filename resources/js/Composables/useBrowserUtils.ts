import { onMounted, ref } from "vue";

const isMobile = ref(false);
export function useBrowserUtils() {

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
