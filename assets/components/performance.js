import {from, toPairs} from "@develia/commons";

export default {
    props: {
        series: {
            default: []
        },
        jobTypes: {
            default: []
        }
    },
    data() {
        return {
            "tabs": null,
            "chartSeries": [],
            "options": {
                chart: {
                    type: 'line',
                    height: 350,
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                    },
                    toolbar: {
                        show: true
                    }
                },
                series: [],
                xaxis: {
                    type: 'datetime',
                    title: {
                        text: 'Fecha'
                    }
                },
                yaxis: {
                    title: {
                        text: 'Puntos'
                    }
                },
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    enabled: true,
                    x: {
                        format: 'dd MMM yyyy'
                    }
                }
            }
        }
    },
    watch: {
        series(item) {
            this.chartSeries = this.convert(item);
        },
        jobTypes(item) {
            this.chartSeries = this.convert(this.series);
        }

    },
    methods: {
        convert(items) {

            let series = [];

            for (let pair of toPairs(items)) {


                let date = new Date(pair.key).getTime();
                if (isNaN(date))
                    continue;

                for (let pair2 of from(pair.value).filter(x => !isNaN(parseInt(x.key)))) {


                    for (let pair3 of toPairs(pair2.value)) {

                        let name = (from(this.jobTypes).first(x => x.id == pair2.key)?.description + " (" + pair3.key + ")");
                        let serie = from(series).first(x => x.name == name);

                        if (!serie) {

                            serie = {name: name, data: []};
                            series.push(serie);
                        }

                        serie.data.push({
                            x: date,
                            y: pair3.value
                        });
                    }


                }


            }

            return series;

        }
    },
    template: `
      <div>
        <div class="row">
          <div class="col-md-3">
            <v-tabs v-model="tabs" direction="vertical">
              <v-tab v-for="jobType in $root.jobTypes" :value="jobType.id">
                {{ jobType.description }}
              </v-tab>
            </v-tabs>
          </div>
          <div class="col-md-9">
            <v-tabs-window v-model="tabs">
              <v-tabs-window-item v-for="jobType in $root.jobTypes" :value="jobType.id">
                <apexchart :series="chartSeries" :options="options"></apexchart>
              </v-tabs-window-item>
            </v-tabs-window>
          </div>
        </div>

      </div>`
}