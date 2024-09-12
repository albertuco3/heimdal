import {isFunction, promisify} from '@develia/commons'

export default {
    props: {
        "data": {
            type: Array,
            default: [],
        },
        "identifier": null,
        "captionField": {
            type: [Function, String],
            default: null
        },
        "onCreating": {
            type: Function,
            default: null
        },
        "onCreate": {
            type: Function,
            default: null
        },
        "onDeleting": {
            type: Function,
            default: null
        },
        "onSelect": {
            type: Function,
            default: null
        },
        "onDelete": {
            type: Function,
            default: null
        },
        "onUpdating": {
            type: Function,
            default: null
        },
        "onUpdate": {
            type: Function,
            default: null
        },
        "allowCreation": {
            type: Boolean,
            default: true
        },
        "allowEdition": {
            type: Boolean,
            default: true
        },
        "allowDeletion": {
            type: Boolean,
            default: true
        }
    },
    data() {
        return {

            "selectedIndex": -1,
            "creating": false,
            "selected": null,
            "editing": false,
            "original": null
        }
    },
    methods: {
        isSelected(item) {
            return item && this.selected && item[this.identifier] === this.selected[this.identifier];
        },
        getLabel(item) {
            if (this.identifier)
                return isFunction(this.captionField) ? this.captionField(item) : item[this.captionField];
            return item;
        },
        select(item) {
            if (!this.editing) {

                if (this.onSelect == null || this.onSelect(item)) {
                    this.selected = item;
                    this.original = structuredClone(this.selected);
                }


            }

        },
        async accept() {
            if (this.editing) {
                if (this.creating) {

                    if (this.onCreating ? await promisify(() => this.onCreating(this.selected)) : true) {
                        if (this.onCreate) {
                            await promisify(() => this.onCreate(this.selected));
                            this.data.push(this.selected);
                            this.original = structuredClone(this.selected);
                        }

                    }

                } else {

                    if (this.onUpdating ? await promisify(() => this.onUpdating(this.selected)) : true) {
                        if (this.onUpdate) {
                            await promisify(() => this.onUpdate(this.selected))
                            this.original = structuredClone(this.selected);
                        }

                    }
                }

                this.creating = false;
                this.editing = false;
                this.original = null;


            }

        },
        begin() {
            if (this.allowEdition && !this.editing) {
                this.editing = true;
            }

        },
        findById(id) {
            let index = this.data.findIndex(x => x[this.identifier] === id);
            if (index !== -1) {
                return this.data[index];
            }
            return null;
        },
        cancel() {

            if (this.editing && this.original != null) {
                let index = this.data.findIndex(x => x[this.identifier] === this.original[this.identifier]);
                if (index !== -1) {
                    this.data[index] = this.original;
                    this.selected = this.original;
                }
            }

            this.original = null;
            this.creating = false;
            this.editing = false;

        },
        create() {

            if (this.allowCreation) {
                this.creating = true;
                this.editing = true;
                this.selected = {};
            }


        },
        async remove(item) {

            if (this.allowDeletion && item != null) {

                let index = this.data.findIndex(x => x[this.identifier] === item[this.identifier]);
                if (index === -1)
                    return;


                if (this.onDeleting ? await promisify(() => this.onDeleting(this.selected)) : true) {
                    if (this.onDelete)
                        await promisify(() => this.onDelete(this.selected))

                    if (this.selected[this.identifier] === this.data[index][this.identifier]) {
                        this.selected = null;
                    }

                    this.data.splice(index, 1);
                }
            }


        }
    },
    template: `
      <div class="row">
        <div class="col-md-3">
          <div class="card" :style="{opacity: editing ? 0.5 : 1}">
            <div class="list-group list-group-flush" style="overflow-y: auto;max-height: 60vh">
              <div class="list-group-item"
                   :class="{'list-group-item-action cursor-pointer': !editing}"
                   v-for="item in data"
                   @click="select(item)">

                <b v-if="isSelected(item)">{{ getLabel(item) }}</b> <span v-else>{{ getLabel(item) }}</span>

              </div>
            </div>
            <div class="p-2 d-flex flex-row-reverse" v-if="allowCreation">
              <button type="button"
                      class="btn btn-primary btn-sm"
                      @click="create()"
                      :disabled="editing">Nuevo/a
              </button>
            </div>
          </div>
        </div>
        <div class="col-md-9">
          <div class="card">
            <template v-if="selected">
              <div class="card-header">
                <b>{{ getLabel(selected) }} </b>
              </div>
              <div v-if="selected" class="card-body">
                <slot v-bind:selected="selected" v-bind:begin="begin">

                </slot>
                <div class="d-flex justify-content-between mt-4 ">
                  <button type="button"
                          class="btn btn-outline-danger"
                          @click="remove(selected)"
                          :disabled="editing"
                          v-if="allowDeletion">
                    <i class="fa fa-trash" style="margin-right: 10px"></i> Eliminar
                  </button>
                  <div class="d-flex gap-3" v-if="allowEdition">
                    <button type="button" class="btn btn-primary" @click="accept()" :disabled="!editing"><i class="fa fa-save" style="margin-right: 10px"></i>
                      Aceptar
                    </button>
                    <button type="button" class="btn btn-secondary" @click="cancel()" :disabled="!editing">
                      <i class="fa fa-cancel" style="margin-right: 10px"></i> Cancelar
                    </button>
                  </div>
                </div>

              </div>
            </template>
          </div>
        </div>
      </div>`
}