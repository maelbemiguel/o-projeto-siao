<script setup>
defineOptions({
    inheritAttrs: false
})

defineProps({
    label:       { type: String,  required: true },
    modelValue:  { type: [String, Number], default: '' },
    type:        { type: String,  default: 'text' },
    placeholder: { type: String,  default: '' },
    required:    { type: Boolean, default: false },
    error:       { type: String,  default: '' },
})
defineEmits(['update:modelValue'])
</script>

<template>
    <div class="form-field">
        <label>{{ label }}<span v-if="required" class="req">*</span></label>
        <input
            v-bind="$attrs"
            :type="type"
            :value="modelValue"
            :placeholder="placeholder"
            @input="$emit('update:modelValue', $event.target.value)"
        />
        <span v-if="error" class="field-error">{{ error }}</span>
    </div>
</template>

<style scoped>
.form-field { display: grid; gap: 6px; }
label { font-size: 13px; font-weight: 600; color: #273142; }
.req { color: #dc2626; margin-left: 3px; }
input {
    height: 44px; border: 1px solid var(--line); border-radius: 10px;
    padding: 0 14px; font-size: 14px; color: var(--ink); outline: none;
    transition: border-color .2s, box-shadow .2s;
}
input:focus { border-color: var(--green); box-shadow: 0 0 0 3px rgba(0,239,31,.1); }
.field-error { font-size: 12px; color: #b42318; }
</style>
