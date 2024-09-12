export default {
    props: {
        maxHeight: {type: String, default: null},
        minHeight: {type: String, default: "50px"},
    },
    data() {

        return {
            items: []
        }
    },
    methods: {
        async refresh() {

            let self = this;

            try {

                if (self.$jEl)
                    self.$jEl.block();

                let response = await fetch("/api/v1/box/technicians-summary/", {method: 'GET'});

                if (response.ok) {
                    self.items = await response.json();
                }

            } finally {
                if (self.$jEl)
                    self.$jEl.unblock();
            }


        }
    },
    async mounted() {
        await this.refresh();
    },
    template: `
      <div style="overflow: auto" :style="{ 'min-height': minHeight, 'max-height': maxHeight}" v-cloak>
        <table class="table table-compact">
          <thead>
            <tr>
              <th>
                <button type="button" class="btn btn-outline-primary btn-xs" @click="refresh"><i class="fa fa-refresh"></i></button>
              </th>
              <th style="text-align: right;width: 75px">
                <i class="fa fa-wrench"></i>
              </th>
              <th style="text-align: right;width: 75px">
                <i class="fa fa-clock"></i>
              </th>

            </tr>
          </thead>
          <tbody>
            <tr v-for="item in items">
              <td>

                {{ item.fullName }}
              </td>
              <td style="text-align: right">
                {{ item.n_jobs }}
              </td>
              <td style="text-align: right">
                {{ item.time }}m
              </td>

            </tr>
          </tbody>
        </table>
      </div>
    `
}