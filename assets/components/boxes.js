import axios from "axios";
import {from} from "@develia/commons";
import toastr from "toastr";
import _ from 'lodash'

export default {
    data() {

        return {
            "serialNumber": "",
            "inbox": [],
            "mybox": [],
            "outbox": [],
            "tempbox": [],
            "parkbox": [],
            "showSendDialog": false,
            "showParkDialog": false,
            "dialogMessages": [],
            "performance": null,
            "activeArticle": null,
        }
    },
    computed: {
        /*classes() {
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
        }*/
        showMessageDialog: {
            get() {
                return this.dialogMessages.length > 0;
            },
            set(value) {
                if (!value) {
                    this.clearMessages();
                }
            }
        }
    },

    methods: {

        async park() {

            try {

                this.$jEl.block();

                for (const item of this.parkbox) {

                    let response = await fetch('/api/v1/box/park/' + item["id"], {method: 'POST'});
                    let data = await response.json();
                    await this.refresh(data);
                }


                toastr.success("Artículos enviados a bunker.", "Éxito");

            } catch (error) {
                toastr.error(error, "Error");
            } finally {

                this.showParkDialog = false;
                this.$jEl.unblock();
            }


        },
        async send() {
            let self = this;
            self.showSendDialog = false;

            for (let item of self.tempbox) {
                let body = new FormData();
                body.append('jobType', item.next_job_type_id);
                body.append('serialNumber', item.serial_number);
                if (item.receiver_id)
                    body.append('to', item.receiver_id);

                let response = await fetch('/api/v1/box/send/' + item.id, {
                    method: 'POST',
                    body: body
                });

                let responseData = await response.json()
                await self.refresh(responseData);
            }

        },
        clearMessages() {
            this.dialogMessages = [];
        },
        async refresh(data = null) {

            let self = this;


            if (data) {
                self.inbox = data.inbox;
                self.mybox = data.mybox;
                self.outbox = data.outbox;
                self.performance = data.performance;
                if (data.message) {
                    self.dialogMessages.push(data.message);
                }

            } else {

                try {

                    self.$jEl.block();


                    let response = await axios.get('/api/v1/box/', {method: 'GET'});


                    await self.refresh(response.data);

                } finally {
                    self.$jEl.unblock();
                }

            }
        },
        async imei(imei) {


            if (this.showParkDialog) {

                const item = from(this.mybox).first(x => x.serial_number == imei);
                if (item && from(this.parkbox).first(x => x.serial_number == imei) === undefined) {
                    this.parkbox.push(item);
                    return;
                }

            } else {

                let item = from(this.inbox).first(x => x.serial_number == imei);

                if (item) {

                    try {

                        this.$jEl.block();

                        let response = await fetch('/api/v1/box/receive/' + item["id"], {method: 'POST'});
                        await this.refresh(await response.json());
                        return;

                    } finally {
                        this.$jEl.unblock();
                    }

                }

                item = from(this.tempbox).first(x => x.serial_number == imei);
                if (item) {
                    _.pull(this.tempbox, item);
                    return;
                }

                item = from(this.mybox).first(x => x.serial_number == imei);
                if (item) {

                    if (!this.showSendDialog)
                        this.tempbox = [];

                    this.tempbox.push(item);
                    this.showSendDialog = true;
                    return;

                }

            }

            toastr.warning("Artículo " + imei + " no encontrado.", "Advertencia");


        }
    },
    async mounted() {
        const self = this;
        window.addEventListener('keydown', function (event) {

            const focusedElement = document.activeElement;

            if (focusedElement.tagName === 'INPUT' || focusedElement.tagName === 'TEXTAREA') {
                return;
            }

            if (event.key === "Enter") {
                event.preventDefault();
                self.imei(self.serialNumber);
                self.serialNumber = "";
            } else if (event.key.match(/^\d$/) || event.key.match(/^\w$/) || event.key == '-') {
                event.preventDefault();
                self.serialNumber += event.key;

            } else if (event.key === "Backspace") {
                event.preventDefault();
                self.serialNumber = self.serialNumber ? self.serialNumber.slice(0, self.serialNumber.length - 1) : "";
            }

        });

        await this.refresh();
    },
    template: `
      <div>
        <div class="row">
          <div class="col-xl-12  mb-3">
            <div class="card p-2 ">
              <div class="d-flex ga-2">
                <button type="button" class="btn btn-default flex-grow-0" @click="showParkDialog=true"><i class="fa fa-lock"></i></button>
                <input type="text" v-model="serialNumber" readonly="readonly" class="form-control flex-grow-1">
                <span v-if="performance != null"> Rendimiento: <b>{{ performance }}</b></span>
              </div>
            </div>
          </div>

          <div class="col-xl-6 col-lg-12">
            <div class="card" style="margin-bottom: 20px">
              <div class="card-header">
                <h5 class="p-0 m-0">Bandeja de entrada</h5>
              </div>

              <Box :items="inbox" ref="inbox" :show-receiver="false" :show-priority="true"></Box>
            </div>

            <div class="card" style="margin-bottom: 20px">

              <div class="card-header">
                <h5 class="p-0 m-0">Bandeja de salida</h5>
              </div>

              <Box :items="outbox" ref="outbox" :show-owner="false" :show-priority="true"></Box>
            </div>
          </div>
          <div class="col-xl-6  col-lg-12">

            <div class="card" style="margin-bottom: 20px">

              <div class="card-header">
                <h5 class="p-0 m-0">Mi bandeja</h5>
              </div>

              <Box :items="mybox" ref="mybox" :show-owner="false" :show-receiver="false" :timer="true" :show-priority="true"></Box>
            </div>
          </div>

        </div>


        <v-dialog v-model="showParkDialog" max-width="calc(100% - 20px)" @after-leave="parkbox = []" width="60%">
          <v-card title="Enviar a bunker">
            <v-card-text>
              <Box :items="parkbox" :show-owner="false" :read-only="true" :show-footer="true" :show-receiver="false"></Box>
            </v-card-text>
            <v-card-actions>

              <v-btn text="Enviar a bunker" @click="park()" class="btn btn-primary"></v-btn>
              <v-btn text="Cancelar" @click="showParkDialog = false" class="btn btn-secondary"></v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>

        <v-dialog v-model="showMessageDialog" max-width="calc(100% - 20px)" width="60%">
          <v-card>
            <v-card-title>Pedido completado</v-card-title>
            <v-card-text>
              <div v-for="(message, index) in dialogMessages" :key="index">{{ message }}</div>
            </v-card-text>
          </v-card>
        </v-dialog>

        <v-dialog v-model="showSendDialog" max-width="calc(100% - 20px)" @after-leave="tempbox = []" width="60%">

          <v-card title="Envio">
            <v-card-text>
              <div class="row">
                <div class="col-xl-3">
                  <TechniciansSummary :max-height="'calc(100vh - 100px)'"></TechniciansSummary>
                </div>
                <div class="col-xl-9" style="display: flex;flex-direction: column;gap: 20px;justify-content: space-between">
                  <div class="row">
                    <div class="col-md-12">
                      <Box ref="tempbox" :items="tempbox" :show-owner="false" :read-only="false" :show-footer="true" ></Box>
                    </div>
                  </div>
                  <div class="row" v-if="false">
                    <div class="col-md-4">Asignar a todos:</div>
                    <div class="col-md-4"><select class="form-control" v-on:change="setAllJobTypes">
                      <option value="" selected="selected"></option>
                      <option v-for="option in $root.jobTypes" :key="option.id" :value="option.id" v-html="option.description">
                      </option>
                    </select>
                    </div>
                    <div class="col-md-4">
                      <select class="form-control" v-on:change="setAllTechnicians">
                        <option value="" selected="selected"></option>
                        <option v-for="option in $root.technicians"
                                :key="option.id"
                                :value="option.id"
                                v-html="option.firstName + ' ' + option.lastName">

                        </option>

                      </select>
                    </div>
                  </div>
                </div>
              </div>
            </v-card-text>
            <v-card-actions>

              <v-btn class="btn btn-primary" @click="send()" text="Enviar"></v-btn>
              <v-btn class="btn btn-default" @click="tempbox = []" text="Reset"></v-btn>
              <v-btn class="btn btn-secondary" @click="showSendDialog = false" text="Cancelar"></v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </div>
    `
}