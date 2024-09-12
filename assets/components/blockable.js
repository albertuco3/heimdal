export default {
    props: {
        modelValue: {
            default: false
        }
    },
    watch: {
        modelValue(newVal) {
            if (newVal)
                this.$jEl.block();
            else
                this.$jEl.unblock();
        }
    },
    template: `
      <div>
        <slot></slot>
      </div>`
}