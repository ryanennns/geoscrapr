import { onBeforeUnmount, onMounted, ref } from "vue";

export function useDarkMode() {
    const isDark = ref(localStorage.getItem("darkMode") === "true");

    const observer = new MutationObserver(() => {
        isDark.value = document.documentElement.classList.contains("dark");
    });

    onMounted(() => {
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ["class"],
        });
    });

    onBeforeUnmount(() => {
        observer.disconnect();
    });

    return { isDark };
}
