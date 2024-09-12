import axios from "axios";
import {from, Timer, TimeSpan} from "@develia/commons";
import _ from "lodash";

export default {
    props: {
        items: {type: Array, default: []},
        showReceiver: {type: Boolean, default: true},
        showOwner: {type: Boolean, default: true},
        showFooter: {type: Boolean, default: false},
        showPriority: {type: Boolean, default: false},
        readOnly: {type: Boolean, default: true},
        timer: {type: Boolean, default: false},
    },
    computed: {
        groups() {
            return _.groupBy(this.items, x => x.delivery_note_id);
        },
        activeItem() {
            return from(this.items).first(x => x.active);
        },
        priorityBackgroundColor() {
            return (item) => {
                switch (item.priority_value) {
                    case 200:
                        return 'red'; // Rojo
                    case 100:
                        return 'orange'; // Naranja
                    case 0:
                        return 'yellow'; // Amarillo
                    case -100:
                        return 'limegreen'; // Verde limón
                    case -200:
                        return 'green'; // Verde
                    default:
                        return 'transparent'; // O el color que prefieras para valores no contemplados
                }
            };
        }
    },
    watch: {
        items(newValue) {
            if (this.timer) {
                if (from(newValue).first(x => x.active) && !this._timer.running) {
                    this._timer.start();
                } else if (this._timer.running) {
                    this._timer.stop();
                }
            }
        }
    },
    created() {
        const self = this;
        this._timer = new Timer(() => {
            if (self.activeItem) {
                self.activeItem.timer = self.activeItem.timer == null ? 0 : self.activeItem.timer + 1;
            }
        }, 1000);
    },
    methods: {

        setAllTechnicians(event) {
            const newValue = event.target.value;
            if (newValue)
                for (let item of this.items) {
                    item.receiver_id = newValue;
                }
        },
        setAllJobTypes(event) {
            const newValue = event.target.value;
            if (newValue)
                for (let item of this.items) {
                    if (from(this.$root.getJobTypeTransitions(item.job_type_id)).any(x => x.id == newValue))
                        item.next_job_type_id = parseInt(newValue);
                }
        },
        formatTime(seconds) {

            return TimeSpan.fromSeconds(seconds).format("mm:ss");
        },
        async startTimer(item) {
            try {
                this.$jEl.block();

                await axios.post("/api/v1/box/start-timer/" + item.id);

                if (this.activeItem) {

                    for (const tmp of this.items) {
                        tmp.active = false;
                    }

                }

                item.active = true;
                this._timer.start();
            } finally {
                this.$jEl.unblock();
            }


        },
        async stopTimer(item) {
            try {
                this.$jEl.block();

                await axios.post("/api/v1/box/stop-timer")
                for (const tmp of this.items) {
                    tmp.active = false;
                }
                this._timer.stop();

            } finally {
                this.$jEl.unblock();
            }


        },
        async updatePriority(item) {
            try {
                this.$jEl.block();

                let body = new FormData();
                body.append('newPriorityValue', item.priority_value);

                await fetch('/api/v1/box/update-priority/' + item.id, {
                    method: 'POST',
                    body: body
                });
            } finally {
                this.$jEl.unblock();
            }
        }
    },
    /*computed: {
        classes() {
            if (this.hasErrors)
                return "eve-error-bg";
            if (this.hasWarnings)
                return "eve-warning-bg";

            return "";

        },
        hasErrors() {
            return this.errors && this.errors.filter($x => $x).length > 0;
        },
        hasWarnings() {
            return this.warnings && this.warnings.filter($x => $x).length > 0;
        }
    },*/
    template: `
      <div>
        <table class="table table-compact">
          <thead>
          <tr v-if="showFooter">
            <td colspan="5">
              Nº de artículos: <b>{{ items ? items.length : 0 }}</b>
            </td>
          </tr>
          <tr>
            <th>
              Albarán
            </th>
            <th>
              IMEI
            </th>
            <th>
              Artículo
            </th>

            <th v-if="showOwner">
              Propietario
            </th>
            <th>
              <span v-if="readOnly">Tarea</span> <span v-else>Próxima tarea</span>
            </th>

            <th v-if="showReceiver">
              Receptor
            </th>

            <th v-if="showPriority">
              Prioridad
            </th>
            <th v-if="timer" style="text-align: center;width: 50px">
              <i class="fa fa-clock"></i>
            </th>
           
          </tr>
          </thead>
          <tbody>
          <tr v-if="!readOnly" style="background-color: #f7f7f7;">
            <td style="text-align: right" :colspan="showOwner ? 4 : 3">
              Asignar a todas las lineas <i class="fa fa-arrow-right" style="margin: 5px;"></i>
            </td>
            <td v-if="showOwner"></td>
            <td>
              <select class="form-control" @change="setAllJobTypes">
                <option value="">
                </option>
                <option v-for="option in $root.jobTypes" :key="option.id" :value="option.id" v-html="option.description">
                </option>
              </select>
            </td>
            <td v-if="showReceiver">
              <select class="form-control" @change="setAllTechnicians">
                <option value="">
                </option>
                <option v-for="option in $root.technicians" :key="option.id" :value="option.id" v-html="option.firstName + ' ' + option.lastName">

                </option>
              </select>
            </td>
          </tr>
          <template v-for="(group,i) in groups">
            <tr v-for="(item,j) in group">
              <td v-if="j == 0" :rowspan="group.length">
                <div v-html="item.delivery_note_id"></div>
                <div><small v-html="item.customer"></small></div>
              </td>
              <td>
              <serial-number-dialog :serial-number="item.serial_number"></serial-number-dialog>
</td>
              <td>
                <div v-html="item.code"></div>
                <div><small v-html="item.description"></small></div>
              </td>

              <td v-if="showOwner">
                <div v-if="item.parked"><b>Bunker</b></div>
                <div v-html="item.owner_display_name"></div>
              </td>
              <td>
                <div v-if="readOnly">{{ item.job_type_description }}</div>
                <div v-else>
                  <select class="form-control" v-model="item.next_job_type_id">
                    <option v-for="option in $root.getJobTypeTransitions(item.job_type_id)" :key="option.id" :value="option.id" v-html="option.description">
                    </option>
                  </select>
                </div>
              </td>

              <td v-if="showReceiver">
                <div v-html="item.receiver_display_name" v-if="readOnly"></div>
                <select class="form-control"
                        v-else
                        v-model="item.receiver_id"
                        :disabled="item.next_job_type_id == null || $root.getJobType(item.next_job_type_id).finishes">
                  <option v-for="option in $root.technicians" :key="option.id" :value="option.id" v-html="option.firstName + ' ' + option.lastName">

                  </option>
                </select>

              </td>

              <td v-if="showPriority" :style="{ backgroundColor: priorityBackgroundColor(item) }">
                <div>
                  <select class="form-control" v-model="item.priority_value" @change="updatePriority(item)">
                    <option v-for="option in $root.priorities" :key="option.value" :value="option.value" v-html="option.name">
                    </option>
                  </select>
                </div>
              </td>
              <td v-if="timer">
                <div class="d-flex flex-column gap-1 align-items-center p-1">
                  <div>
                    {{ formatTime(item.timer) }}
                  </div>
                  <div>
                    <button type="button" class="btn btn-xs" @click="stopTimer(item)" v-if="item.active">
                      <i class="fa fa-pause"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-primary" @click="startTimer(item)" v-else>
                      <i class="fa fa-play"></i>
                    </button>
                  </div>
                </div>

              </td>
            </tr>

          </template>

          </tbody>
        </table>
      </div>
    `
}