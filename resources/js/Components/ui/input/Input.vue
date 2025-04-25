<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { useVModel } from '@vueuse/core';
import { cn } from '@/lib/utils';

const props = defineProps<{
  defaultValue?: string | number
  modelValue?: string | number
  class?: HTMLAttributes['class']
  isError?: boolean
}>();

const emits = defineEmits<{
  (e: 'update:modelValue', payload: string | number): void
}>();

const modelValue = useVModel(props, 'modelValue', emits, {
  passive: true,
  defaultValue: props.defaultValue,
});
</script>

<template>
  <input v-model="modelValue" :class="cn(
    'flex w-full rounded-md border border-prijma-stroke h-[57px] bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
    isError && 'ring-prijma-danger border-prijma-danger focus-visible:border-0 focus-visible:ring-prijma-danger',
    props.class)"
    >
</template>
