<!-- src/Components/DateSelector.vue -->
<template>
    <div class="w-40">
        <div class="relative">
            <Datepicker
                v-model="internalDate"
                :enable-time-picker="false"
                :allowed-dates="availableDates"
                @update:model-value="emitUpdate"
                placeholder="Pick a date"
                auto-apply
                clearable
            />
        </div>
    </div>
</template>

<script setup>
import {ref, watch} from 'vue'
import Datepicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'

const props = defineProps({
    modelValue: Date,
    availableDates: Array,
})

const emit = defineEmits(['update:modelValue'])

const internalDate = ref(props.modelValue)

watch(() => props.modelValue, (val) => {
    internalDate.value = val
})

const emitUpdate = (val) => {
    emit('update:modelValue', val)
}
</script>
