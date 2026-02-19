<!-- Footer Component -->
<template>
    <footer class="bg-gray-900 text-white py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-8">
                <div class="mb-6 md:mb-0">
                    <h3 class="text-lg font-semibold mb-4">About GeoScrapr</h3>
                    <p class="text-gray-300 text-sm">
                        Every day at midnight Eastern Time, this application
                        scrapes the official GeoGuessr APIs for updated player
                        and team rating data. This is then aggregated into
                        rating changes and daily snapshots. For more information
                        about the implementation details of this app, see the
                        open source GitHub repository.
                    </p>
                </div>

                <div class="mb-6 md:mb-0">
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li>
                            <p
                                class="text-gray-300 hover:cursor-default text-sm"
                            >
                                <a href="/">Home</a>
                            </p>
                        </li>
                        <li>
                            <p
                                class="text-gray-300 hover:cursor-default text-sm"
                            >
                                <s>Player Search</s>
                            </p>
                        </li>
                        <li>
                            <p
                                class="text-gray-300 hover:cursor-default text-sm"
                            >
                                <s>Stats Archive</s>
                            </p>
                        </li>
                    </ul>
                </div>

                <div class="mb-6 md:mb-0">
                    <h3 class="text-lg font-semibold mb-4">Resources</h3>
                    <ul class="space-y-2">
                        <li class="mb-1">
                            <a
                                target="_blank"
                                href="https://geoguessr.com"
                                class="text-gray-300 hover:text-white text-sm"
                            >
                                Official GeoGuessr Site
                            </a>
                        </li>
                        <li class="mb-1">
                            <a
                                target="_blank"
                                href="https://github.com/ryanennns/geoscrapr"
                                class="text-gray-300 hover:text-white text-sm"
                            >
                                GitHub Repository
                            </a>
                        </li>
                        <li class="mb-1">
                            <a
                                target="_blank"
                                href="https://en.wikipedia.org/wiki/Elo_rating_system"
                                class="text-gray-300 hover:text-white text-sm"
                            >
                                Rating System, Explained
                            </a>
                        </li>
                        <li class="mb-1">
                            <a
                                target="_blank"
                                href="https://www.plonkit.net/beginners-guide"
                                class="text-gray-300 hover:text-white text-sm"
                            >
                                Learn GeoGuessr
                            </a>
                        </li>
                    </ul>
                </div>

                <div>
                    <div class="flex space-x-4">
                        <h3 class="text-lg font-semibold mb-4">Connect</h3>
                        <a
                            target="_blank"
                            href="https://github.com/ryanennns"
                            class="text-gray-300 hover:text-white"
                        >
                            <span class="sr-only">GitHub</span>
                            <svg
                                class="h-6 w-6"
                                fill="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"
                                    clip-rule="evenodd"
                                ></path>
                            </svg>
                        </a>
                    </div>
                    <p class="text-sm text-gray-300">
                        Questions? Feedback? Found a bug?
                        <br />
                        <a
                            target="_blank"
                            href="mailto:ryanennns@gmail.com"
                            class="text-indigo-300 hover:text-indigo-200"
                        >
                            Email me!
                        </a>
                    </p>
                </div>
            </div>

            <div class="mt-8 pt-8 border-t border-gray-700">
                <div
                    class="flex flex-col md:flex-row justify-between items-center"
                >
                    <p class="text-sm text-gray-400">
                        This website is not affiliated with GeoGuessr in any
                        capacity.
                    </p>
                    <div class="mt-4 md:mt-0">
                        <ul class="flex space-x-6">
                            <li>
                                <a
                                    target="_blank"
                                    class="text-xs text-gray-400 hover:cursor-default"
                                >
                                    I don't have any of these lol -->
                                </a>
                            </li>
                            <li>
                                <a
                                    target="_blank"
                                    href="#"
                                    class="text-xs text-gray-400 hover:text-gray-300"
                                >
                                    <s>Privacy Policy</s>
                                </a>
                            </li>
                            <li>
                                <a
                                    target="_blank"
                                    href="#"
                                    class="text-xs text-gray-400 hover:text-gray-300"
                                >
                                    <s>Terms of Service</s>
                                </a>
                            </li>
                            <li>
                                <a
                                    target="_blank"
                                    href="#"
                                    class="text-xs text-gray-400 hover:text-gray-300"
                                >
                                    <s>Data Policy</s>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="mt-4 text-center text-xs text-gray-500">
                    Last data update: {{ date }} | API Status:
                    <span v-show="successful" class="text-green-400"
                        >Operational</span
                    >
                    <span v-show="!successful" class="text-red-500"
                        >Busted</span
                    >
                </div>
            </div>
        </div>
    </footer>
</template>

<script setup lang="ts">
import { onMounted, ref } from "vue";
import { useApiClient } from "@/Composables/useApiClient.ts";

const date = ref<string>("");
const successful = ref<boolean>(false);

const { getLastUpdated } = useApiClient();

onMounted(async () => {
    const lastUpdated = await getLastUpdated();

    if (lastUpdated.error) {
        successful.value = false;

        return;
    }

    if (lastUpdated?.data?.date === undefined) {
        successful.value = false;

        return;
    }

    successful.value = true;
    date.value = formatDate(new Date(lastUpdated.data.date));
});

const formatDate = (date: Date): string => {
    return new Intl.DateTimeFormat("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    }).format(date);
};
</script>
