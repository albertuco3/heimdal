import axios from "axios";
import toastr from 'toastr'
import _ from "lodash";

export default {
    data() {
        return {

            "technicianJobs": [],
            "items": [],
            "filters": {
                "source": "",
                "orderType": "",
                "company": "",
                "fromDate": null,
                "toDate": null
            }
        }
    },
    methods: {
        setTechnician(item, event) {
            for (const line of item.lines) {
                line.technician = event.target.value;
            }
        },
        setJob(item, event) {
            for (const line of item.lines) {
                line.jobType = event.target.value;
            }
        },
        setPriority(item, event) {
            for (const line of item.lines) {
                line.priority = event.target.value;
            }
        },
        async refresh() {
            let self = this;

            try {
                self.$jEl.block();

                let params = new URLSearchParams({
                    orderType: self.filters.orderType,
                    fromDate: self.filters.fromDate,
                    toDate: self.filters.toDate,
                    company: self.filters.company,
                    source: self.filters.source
                });

                let response = await fetch("/api/v1/box/xgest-importables?" + params.toString());
                self.items = await response.json();
                await self.$refs["techniciansSummary"].refresh();

            } finally {
                self.$jEl.unblock();

                await self.$refs["techniciansSummary"].refresh();
            }


        },
        async _importLine(line, technician, jobType, priority) {


            if (!technician || !jobType)
                return;

            let self = this;

            let formData = new FormData();


            formData.append("serialNumber", line.serialNumber || '');
            formData.append("deliveryNoteId", line.deliveryNoteId || '');
            formData.append("lineNumber", line.lineNumber || '');
            formData.append("technician", technician || '');
            formData.append("jobType", jobType || '');
            formData.append("priority", priority || '');

            const response = await axios.post("/api/v1/box/xgest-import", formData);

            if (response.status === 200) {


                let item = _.find(self.items, x => x.lines.indexOf(line) !== -1);
                if (item) {

                    _.pull(item.lines, line);
                    if (item.lines.length === 0) {
                        _.pull(self.items, item);
                    }

                }

            }

        },
        async importLine(line, technician, jobType, priority) {


            if (!technician)
                return;
            if (!jobType)
                return;

            this.$jEl.block();

            try {
                await this._importLine(line, technician, jobType, priority)
                await this.$refs["techniciansSummary"].refresh();

                toastr.success("Artículo importado.", "Éxito");

            } catch (error) {
                toastr.error(error, "Error");
            } finally {
                this.$jEl.unblock();
            }
        },
        async importItem(item) {


            this.$jEl.block();

            try {

                for (let line of item.lines.slice()) {
                    await this._importLine(line, line.technician, line.jobType, line.priority);
                }

                toastr.success("Artículos importados.", "Éxito");

            } catch (error) {
                toastr.error(error, "Error");
            } finally {
                this.$jEl.unblock();
            }


        }
    },
    template: `
      <div class="row" v-cloak>
        <div class="col-md-3" style="display: flex;flex-direction: column;gap: 20px">
          <div class="card">
            <div class="card-body" style="display: flex;flex-direction: column;gap: 10px">
              <div>
                <label>Tipo de pedido</label>
                <select class="form-control" v-model="filters.orderType">
                  <option value="">Todos</option>
                  <option value="1">Ventas</option>
                  <option value="2">Reparaciones</option>
                </select>
              </div>

              <div>
                <label>Origen</label>
                <select class="form-control" v-model="filters.source">
                  <option value="">Todos</option>
                  <option value="orders">Albaranes</option>
                  <option value="warehouse-15">Almacen 15</option>
                  <option value="warehouse-27">Almacen 27</option>
                </select>
              </div>
              <div>
                <label>Empresa</label>
                <select class="form-control" v-model="filters.company">
                  <option value="" selected="selected">Todos</option>
                  <option value="005">AcelStore</option>
                  <option value="004">AcelCel</option>
                </select>
              </div>
              <div>
                <label>Desde fecha</label>
                <input type="date" v-model="filters.fromDate" class="form-control">
              </div>
              <div>
                <label>Hasta fecha</label>
                <input type="date" v-model="filters.toDate" class="form-control">
              </div>
              <div style="text-align: right">
                <button type="button" @click="refresh()" class="btn btn-primary">Buscar</button>
              </div>

            </div>

          </div>
          <div class="card">
            <TechniciansSummary ref="techniciansSummary" v-cloak></TechniciansSummary>
          </div>
        </div>
        <div class="col-md-9">

          <template v-for="item in items">
            <div class="nav-align-top nav-tabs-shadow mb-4" v-if="item.lines && item.lines.length > 0">
              <ul class="nav nav-tabs" v-if="item.deliveryNoteId">
                <li class="nav-item">
                  <span class="nav-link">
                    <span>Albarán <b>{{ item.deliveryNoteId }}</b></span>
                  </span>
                </li>
              </ul>
              <div class="tab-content p-2">
                <div class="tab-pane active">
                  <div class="row">
                    <div class="col-md-4">

                      <table class="table table-compact">
                        <tbody>
                        <tr v-if="item.companyName">
                          <th>Empresa</th>
                          <td>{{ item.companyName }}</td>
                        </tr>
                        <tr v-if="item.orderNumber">
                          <th>Pedido</th>
                          <td>{{ item.orderNumber }}</td>
                        </tr>
                        <tr v-if="item.customerName">
                          <th>Cliente</th>
                          <td>{{ item.customerName }}</td>
                        </tr>
                        <tr v-if="item.warehouseId">
                          <th>Almacén</th>
                          <td>{{ item.warehouseId }}</td>
                        </tr>
                        </tbody>
                      </table>

                    </div>
                    <div class="col-md-8 p-2">
                      <div class="card">
                        <table class="table table-compact table-bordered">
                          <thead>
                          <tr>
                            <th style="width: 20%;">IMEI</th>
                            <th style="width: 20%;">Código</th>
                            <th style="width: 20%;">Técnico</th>
                            <th style="width: 20%;">Tarea</th>
                            <th style="width: 20%;">Prioridad</th>
                            <th style="width: 50px;"></th>
                          </tr>
                          <tr style="background-color: #f7f7f7">
                            <td colspan="2" style="text-align: right;vertical-align: middle;">
                              Asignar a todas las lineas <i class="fa fa-arrow-right" style="margin: 5px;"></i>
                            </td>

                            <td>
                              <select class="form-control" @change="setTechnician(item,$event)">
                                <option value=""></option>
                                <option v-for="option in $root.technicians"
                                        :key="option.id"
                                        :value="option.id"
                                        v-html="option.firstName + ' ' + option.lastName">

                                </option>

                              </select>
                            </td>
                            <td>
                              <select class="form-control" @change="setJob(item,$event)">
                                <option value=""></option>
                                <option v-for="option in $root.jobTypes" :key="option.id" :value="option.id" v-html="option.description">
                                </option>

                              </select>
                            </td>
                            <td>
                              <select class="form-control" @change="setPriority(item,$event)" value="0">
                                <option v-for="option in $root.priorities" :key="option.value" :value="option.value" v-html="option.name">
                                </option>

                              </select>
                            </td>
                            <td>
                              <button type="button"
                                      @click="importItem(item)"
                                      class="btn-primary btn  btn-sm"
                                      style="width: 100%">
                                <i class="fa fa-forward"></i>
                              </button>
                            </td>
                          </tr>
                          </thead>
                          <tbody>

                          <tr v-for="(line, index) in item.lines">

                            <td>
                                <serial-number-dialog :serial-number="line.serialNumber"></serial-number-dialog>
                            </td>
                            <td>
                              {{ line.articleCode }}
                            </td>
                            <td>
                              <select class="form-control" v-model="line.technician">
                                <option v-for="option in $root.technicians"
                                        :key="option.id"
                                        :value="option.id"
                                        v-html="option.firstName + ' ' + option.lastName">

                                </option>

                              </select>
                            </td>
                            <td>
                              <select class="form-control" v-model="line.jobType">
                                <option v-for="option in $root.jobTypes" :key="option.id" :value="option.id" v-html="option.description">
                                </option>

                              </select>
                            </td>
                            <td>
                              <select class="form-control" v-model="line.priority">
                                <option v-for="option in $root.priorities" :key="option.value" :value="option.value" v-html="option.name">
                                </option>

                              </select>
                            </td>
                            <td>
                              <button type="button"
                                      class="btn-primary btn btn-sm"
                                      style="width: 100%"
                                      @click="importLine(line,line.technician,line.jobType,line.priority)"
                                      :disabled="!line.technician || !line.jobType">

                                <i class="fa fa-play"></i>
                              </button>
                            </td>
                          </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
            </div>
          </template>

        </div>
      </div>


    `
}