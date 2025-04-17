<!-- Footer Component -->
<template>
    <footer class="bg-gray-800 text-white py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-8">
                <div class="mb-6 md:mb-0">
                    <h3 class="text-lg font-semibold mb-4">
                        About GeoGuessr Stats
                    </h3>
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
                                <s>Home</s>
                            </p>
                        </li>
                        <li>
                            <p
                                class="text-gray-300 hover:cursor-default text-sm"
                            >
                                <s>Leaderboards</s>
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
                        <li>
                            <a
                                href="https://geoguessr.com"
                                class="text-gray-300 hover:text-white text-sm"
                                >Official GeoGuessr Site</a
                            >
                        </li>
                        <li>
                            <a
                                href="https://github.com/ryanennns/geoscrapr"
                                class="text-gray-300 hover:text-white text-sm"
                                >GitHub Repository</a
                            >
                        </li>
                        <li>
                            <a
                                href="https://en.wikipedia.org/wiki/Elo_rating_system"
                                class="text-gray-300 hover:text-white text-sm"
                                >Rating System, Explained</a
                            >
                        </li>
                        <li>
                            <a
                                href="https://www.plonkit.net/beginners-guide"
                                class="text-gray-300 hover:text-white text-sm"
                                >Learn GeoGuessr</a
                            >
                        </li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-4">Connect</h3>
                    <div class="flex space-x-4 mb-4">
                        <a href="#" class="text-gray-300 hover:text-white">
                            <span class="sr-only">Twitter</span>
                            <svg
                                class="h-6 w-6"
                                fill="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"
                                ></path>
                            </svg>
                        </a>
                        <a
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
                                    class="text-xs text-gray-400 hover:cursor-default"
                                    >I don't have any of these lol --></a
                                >
                            </li>
                            <li>
                                <a
                                    href="#"
                                    class="text-xs text-gray-400 hover:text-gray-300"
                                    ><s>Privacy Policy</s></a
                                >
                            </li>
                            <li>
                                <a
                                    href="#"
                                    class="text-xs text-gray-400 hover:text-gray-300"
                                    ><s>Terms of Service</s></a
                                >
                            </li>
                            <li>
                                <a
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

const date = ref<string>("");
const successful = ref<boolean>(false);
onMounted(async () => {
    const lastUpdated = await fetch("/last-updated");

    if (!lastUpdated.ok) {
        successful.value = false;

        return;
    }

    successful.value = true;

    const json = await lastUpdated.json();

    date.value = formatDate(new Date(json.date));
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
