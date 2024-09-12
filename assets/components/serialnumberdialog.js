import axios from "axios";
import {objectToFormData} from "@develia/commons";
import toastr from "toastr";

export default {
    data() {
        return {
            "show": false,
            "notes": '',
            "busy": false
        }
    },
    props: {
        serialNumber: {type: String, default: null},
    },
    methods: {
        async showDialog() {

            this.show = true;
            this.busy = true;
            try {
                const response = await axios.get("/api/v1/serial-numbers/" + this.serialNumber);
                if (response.status === 200) {
                    this.notes = response.data.notes;
                }
            } catch {
                toastr.error("Error al recuperar los datos.", "Error");
            } finally {
                this.busy = false;
            }


        },
        async save() {

            this.busy = true;
            try {
                const response = await axios.post("/api/v1/serial-numbers/" + this.serialNumber, objectToFormData({
                    "notes": this.notes
                }));

                if (response.status === 200) {
                    this.show = false;
                    toastr.success("Datos actualizados.", "Éxito");
                }


            } catch {
                toastr.error("Error al actualizar.", "Error");
            } finally {
                this.busy = false;
            }
        }
    },
    template: `

<a @click="showDialog()" href="javascript:void(0)">{{serialNumber}}</a>

<v-dialog v-model="show" @change="load()" max-width="800px">

<v-card :title="serialNumber">

            <v-card-text>
              
              <v-label>Observaciones</v-label>
               <v-textarea v-model="notes" :readonly="busy"></v-textarea>
        
          
            </v-card-text>
         
            <v-card-actions>
              <v-btn class="btn btn-primary" @click="save()" text="Guardar" :disabled="busy"></v-btn>
              <v-btn class="btn btn-secondary" @click="show = false" text="Cancelar" :disabled="busy"></v-btn>
            </v-card-actions>
  
          </v-card>

</v-dialog>`

}