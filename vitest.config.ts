import { defineConfig } from "vitest/config";
import viteConfig from "./vite.config";

export default defineConfig({
    ...viteConfig,
    test: {
        environment: "jsdom",
        globals: true,
        setupFiles: "resources/tests/setup.ts",
    },
});
