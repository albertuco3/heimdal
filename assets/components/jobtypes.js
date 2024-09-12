import {from, objectToFormData} from '@develia/commons'
import axios from "axios";

export default {
    props: {
        'jobTypes': null
    },
    methods: {
        async create(item) {
            try {

                this.$jEl.block();

                let body = objectToFormData(item);
                let response = await fetch("/api/v1/job-types/", {
                    method: 'POST',
                    body: body
                });

                if (!response.ok) {
                    throw response.statusText;
                } else {
                    let updated = await response.json();
                    Object.assign(item, updated)
                }


            } finally {
                this.$jEl.unblock();
            }
        },

        async update(item) {

            try {

                this.$jEl.block();

                let body = objectToFormData(item);
                let response = await fetch("/api/v1/job-types/" + item.id, {
                    method: 'POST',
                    body: body
                });

                if (!response.ok) {
                    throw response.statusText;
                }

            } finally {
                this.$jEl.unblock();
            }

        },
        async remove(item) {
            try {

                this.$jEl.block();
                await axios.delete("/api/v1/job-types/" + item.id, {
                    method: 'DELETE'
                });

                for (const jobType of this.jobTypes) {
                    jobType.transitions = from(jobType.transitions).filter(x => x.fromJob !== item.id && x.toJob !== item.id)
                                                                   .toArray();
                }

            } finally {
                this.$jEl.unblock();
            }
        }
    },
    template: `
      <Navigator :data="jobTypes"
                 v-slot="{selected, begin}"
                 :caption-field="'description'"
                 :identifier="'id'"
                 :on-create="create"
                 :on-update="update"
                 :on-delete="remove">
        <div class="d-flex gap-3 flex-column">
          <div>
            <label class="form-label">Nombre</label>
            <input type="text" v-model="selected.description" @input="begin()" class="form-control">
          </div>

          <div>
            <label class="form-label">¿Saca al artículo de la trazabilidad?</label>
            <div>

              <input type="checkbox" v-model="selected.finishes" @input="begin()">
            </div>
          </div>
          <div v-if="!selected.finishes">
            <label class="form-label">Transiciones</label>
            <div class="card p-2">
              <List v-model="selected.transitions" v-slot="{ item }" @item-created="begin()" @item-removed="begin()">
                <div class="row">
                  <div class="col-md-6">

                    <div class="input-group input-group-sm">
                      <span class="input-group-text">Tarea</span> <select v-model="item.toJob" class="form-control" @change="begin()">
                      <option v-for="job in jobTypes" :value="job.id" v-html="job.description"></option>
                    </select>
                    </div>

                  </div>
                  <div class="col-md-6">

                    <div class="input-group input-group-sm">
                      <span class="input-group-text">Puntos</span>
                      <input type="number" class="form-control" v-model="item.pointsPerCompletion" @input="begin()">
                    </div>

                  </div>
                </div>

              </List>
            </div>

          </div>
        </div>
      </Navigator>`
}