<template>
    <textarea
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
        :class="classes"
    ></textarea>
</template>

<script>
export default {
    props: ['modelValue', 'class'],
    emits: ['update:modelValue'],
    computed: {
        classes() {
            return this.$props.class
        }
    }
}
</script>