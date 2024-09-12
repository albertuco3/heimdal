import {objectToFormData} from '@develia/commons'
import toastr from "toastr";

export default {
    props: {
        "points": {
            type: Array
        },
        "selected": {
            type: Array
        },
    },
    data() {
        return {

            "addingPoints": false,
            "addPointsData": {},
            "busy": false
        }
    },
    methods: {
        beginAddingPoints(item) {
            this.addPointsData.points = null;
            this.addPointsData.reason = null;
            this.addPointsData.dateandtime = null;
            this.addingPoints = true;
        },
        async addPoints() {


            try {

                this.busy++;

                let response = await fetch("/api/v1/users/" + this.selected + "/points", {
                    method: 'POST',
                    body: objectToFormData({
                        points: this.addPointsData.points,
                        reason: this.addPointsData.reason,
                        dateandtime: this.addPointsData.dateandtime
                    })
                });

                if (!response.ok) {
                    toastr.error(response.statusText, "Error");
                    throw response.statusText;
                } else {
                    this.points.push(await response.json());
                    toastr.success("Puntos añadidos", "Éxito");
                    this.addingPoints = false;
                }


            } finally {
                this.busy--;
            }
        }
    },
    template: `
      <blockable :block="busy">
        <div class="d-flex gap-3 flex-column">
          <table class="table">
            <thead>
            <tr>
              <th>
                Puntos
              </th>
              <th>
                Fecha
              </th>
              <th>
                Razón
              </th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="point in points">
              <td>
                {{ point.points }}
              </td>
              <td>
                {{ point.date }}
              </td>
              <td>
                {{ point.reason }}
              </td>
            </tr>
            </tbody>
          </table>
          <button @click="beginAddingPoints" class="btn btn-primary" type="button">
            Añadir puntos
          </button>

        </div>

        <v-dialog v-model="addingPoints" max-width="480">
          <v-card title="Añadir puntos">
            <v-card-text>
              <div>
                <label>Puntos</label>
                <input type="number" class="form-control" v-model="addPointsData.points">
              </div>
              <div>
                <label>Razon</label>
                <input type="text" class="form-control" v-model="addPointsData.reason">
              </div>
              <div>
                <label>Fecha y hora</label>
                <input type="datetime-local" class="form-control" v-model="addPointsData.dateandtime">
              </div>

            </v-card-text>
            <v-card-actions>
              <button type="button" class="btn btn-primary" @click="addPoints()">Añadir</button>
              <button type="button" class="btn btn-secondary" @click="addingPoints = false">Cancelar</button>
            </v-card-actions>
          </v-card>

        </v-dialog>
      </blockable>
    `
}