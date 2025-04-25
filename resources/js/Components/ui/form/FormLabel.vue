<script lang="ts" setup>
import type { LabelProps } from 'radix-vue';
import type { HTMLAttributes } from 'vue';
import { Label } from '@/Components/ui/label';
import { cn } from '@/lib/utils';
import { useFormField } from './useFormField';

const props = defineProps<
  {
    isRequired?: boolean
  }
  & LabelProps
  & { class?: HTMLAttributes['class'] }
>();

const { error, formItemId } = useFormField();
</script>

<template>
  <Label :class="cn(
    isRequired && 'required-field',
    error && 'text-prijma-danger',
    props.class,
  )" :for="formItemId">
    <slot />
  </Label>
</template>
