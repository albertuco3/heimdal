export default {
    props: {
        "modelValue": {
            type: Array,
            default: [],
        },
        "field": '',
        "allowCreation": {
            type: Boolean,
            default: true
        },
        "allowDeletion": {
            type: Boolean,
            default: true
        },
        "allowEdition": {
            type: Boolean,
            default: true
        }
    },
    data() {
        return {}
    },
    methods: {
        create() {
            let item = {};
            this.$emit('item-created', item);
            this.modelValue.push(item)
            this.$emit('update:modelValue', this.modelValue);
        },
        remove(item) {
            if (item == null)
                return;

            let index = this.modelValue.indexOf(item);
            if (index === -1)
                return;

            this.modelValue.splice(index, 1);
            this.$emit('item-removed', item);
            this.$emit('update:modelValue', this.modelValue);
        }
    },
    template: `

      <div class="d-flex gap-2 flex-column">
        <div v-for="item in modelValue">
          <div class="d-flex gap-2">
            <div style="flex-grow: 1">
              <slot v-bind:item="item">
                <input type="text" class="form-control form-control-sm" v-model="item[field]" :readonly="!allowEdition">
              </slot>
            </div>
            <button class="btn btn-outline-danger btn-sm" type="button" @click="remove(item)" :disabled="!allowDeletion"><i class="fa fa-trash"></i></button>
          </div>

        </div>
        <div class="d-flex flex-row-reverse">
          <button type="button" @click="create()" class="btn btn-outline-primary btn-sm" :disabled="!allowCreation"><i class="fa fa-plus"></i>
          </button>
        </div>
      </div>`
}