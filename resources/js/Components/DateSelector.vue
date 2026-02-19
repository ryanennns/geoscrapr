<template>
    <div class="w-72">
        <div class="relative">
            <Datepicker
                v-model="internalDate"
                :enable-time-picker="false"
                :allowed-dates="availableDates"
                @update:model-value="emitUpdate"
                placeholder="Pick a date"
                auto-apply
                :clearable="false"
                :dark="isDark"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, watch } from "vue";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";

interface Props {
    modelValue: Date;
    availableDates: Date[];
}

const props = defineProps<Props>();

const emit = defineEmits(["update:modelValue"]);

const internalDate = ref(props.modelValue);

watch(
    () => props.modelValue,
    (val) => {
        internalDate.value = val;
    },
);

const emitUpdate = (val: Date) => {
    emit("update:modelValue", val);
};

const isDark = ref(document.documentElement.classList.contains("dark"));

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
</script>
