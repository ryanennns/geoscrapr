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
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from "vue";
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
</script>
