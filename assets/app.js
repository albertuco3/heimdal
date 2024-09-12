import {createApp} from 'vue'
import Box from "./components/box.js";
import Boxes from "./components/boxes.js";
import ImportFromXGest from "./components/importfromxgest.js";
import TechniciansSummary from "./components/technicians-summary.js";
import Navigator from "./components/navigator.js";
import List from "./components/list.js";
import JobTypes from "./components/jobtypes.js";
import {from} from "@develia/commons";
import Performance from "./components/performance.js";
import VueApexCharts from "vue3-apexcharts";
import {createVuetify} from "vuetify";
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
//import vuetify from 'vuexy/plugins/vuetify/index'
import './main.scss'
import UserPoints from "./components/userpoints";
import Blockable from "./components/blockable";
import timeSpan from "@develia/vue/src/TimeSpan";
import serialnumberdialog from "./components/serialnumberdialog";


Object.assign($.blockUI.defaults, {
    overlayCSS: {
        backgroundColor: '#f8f7fa',
        opacity: 0.4,
        cursor: 'wait'
    },
    message: '<div class="spinner-border" role="status" style="color: #0F2554"></div>',
    //blockMsgClass: 'card',
    css: {
        //fontSize: '18px',
        //padding: "10px",
        margin: 0,
        width: '30%',
        top: '40%',
        left: '35%',
        textAlign: 'center',
        border: 'none',
        cursor: 'wait',
        color: '#222',
        //boxShadow: '0 0 10px rgba(0,0,0,0.8)'
    }

});

let app = createApp({
    data() {
        return {
            jobTypes: [],
            technicians: [],
        }
    },
    async created() {
        let self = this;

        try {
            self.$jEl.block();

            const results = await Promise.all([
                fetch('/api/v1/job-types/', {method: 'GET'}),
                fetch('/api/v1/users', {method: 'GET'})
            ]);

            self.jobTypes = await results[0].json();
            self.technicians = await results[1].json();
        } finally {
            self.$jEl.unblock();
        }


    },
    methods: {
        getJobType(jobTypeId) {
            return from(this.jobTypes).first(x => x.id === jobTypeId);
        },
        getJobTypeTransitions(jobTypeId) {
            let jobType = this.getJobType(jobTypeId);
            if (!jobType)
                return [];

            return from(jobType.transitions)
                .map(x => from(this.jobTypes).first(y => y.id === x.toJob))
                .orderBy(jobType => jobType.description)
                .toArray();
        },
        toggleMenu() {

            window.Helpers.toggleCollapsed();
            // Enable menu state with local storage support if enableMenuLocalStorage = true from config.js
            if (config.enableMenuLocalStorage && !window.Helpers.isSmallScreen()) {
                try {
                    localStorage.setItem(
                        'templateCustomizer-' + templateName + '--LayoutCollapsed',
                        String(window.Helpers.isCollapsed())
                    );
                    // Update customizer checkbox state on click of menu toggler
                    let layoutCollapsedCustomizerOptions = document.querySelector('.template-customizer-layouts-options');
                    if (layoutCollapsedCustomizerOptions) {
                        let layoutCollapsedVal = window.Helpers.isCollapsed() ? 'collapsed' : 'expanded';
                        layoutCollapsedCustomizerOptions.querySelector(`input[value="${layoutCollapsedVal}"]`).click();
                    }
                } catch (e) {
                }
            }
        }

    },
    computed: {
        priorities() {
            return [
                { name: 'Muy baja', value: -200 },
                { name: 'Baja', value: -100 },
                { name: 'Media', value: 0 },
                { name: 'Alta', value: 100 },
                { name: 'Muy alta', value: 200 }
            ];
        }
    }
}).mixin({
    computed: {
        $jEl() {
            return $(this.$el);
        }
    }
}).use(VueApexCharts)
  .component('Box', Box)
  .component('Boxes', Boxes)
  .component('importfromxgest', ImportFromXGest)
  .component('TechniciansSummary', TechniciansSummary)
  .component('jobtypes', JobTypes)
  .component('Navigator', Navigator)
  .component('List', List)
  .component('Performance', Performance)
  .component('UserPoints', UserPoints)
  .component('blockable', Blockable)
  .component('timespan', timeSpan)
  .component('serialNumberDialog', serialnumberdialog);

app.use(createVuetify({
    components,
    directives
}));

app.mount('#app');
