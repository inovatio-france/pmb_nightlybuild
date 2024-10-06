<template>
    <div>
        <div class="mb-s">
            <form-display :settings="settings" :display_formats="widget_type.display_formats"></form-display>
        </div>

        <div class="mb-s">
            <fieldset class="widget-menu-method widget-fieldset">
                <legend class="widget-legend">{{ messages.get('dashboard', 'form_pmb_menu_legend') }}</legend>

                <div class="mb-s">
                    <label class="widget-form-label visually-hidden" for="widget-menu-opt-module">
                        {{ messages.get('dashboard', 'form_pmb_menu_label') }}
                    </label>
                    <select multiple id="widget-menu-opt-module" v-model="selectedMenu" @change="addMenu" size="10" >
                        <template v-for="menu in configuration">
                            <option class="widget-menu-optgroup" :key="menu.hash" :value="menu.hash" >
                                {{ menu.label}}
                            </option>
                            <optgroup v-for="section in menu.sections" :label="section.label">
                                <option v-for="tab in section.tabs" :key="tab.hash" class="widget-menu-opt-tab" :value="tab.hash">
                                    {{ tab.label }}
                                </option>
                            </optgroup>
                        </template>
                    </select>
                </div>
            </fieldset>
        </div>
        <div class="mb-s">
            <fieldset class="widget-menu-method widget-fieldset">
                <legend class="widget-legend">{{ messages.get('dashboard', 'form_cst_menu_legend') }}</legend>

                <div class="mb-s">
                    <label class="widget-form-label" for="widget-menu-label-custom-module">
                        {{ messages.get('dashboard', 'form_cst_menu_label') }}
                    </label>
                    <input id="widget-menu-label-custom-module" type="text" v-model="customLabel">
                </div>
                <div class="mb-s">
                    <label class="widget-form-label" for="widget-menu-url-custom-module">
                        {{ messages.get('dashboard', 'form_cst_menu_link') }}
                    </label>
                    <input id="widget-menu-url-custom-module" type="text" v-model="customLink" placeholder="https://exemple.com" >
                    <button 
                        :disabled="customLabel === '' || customLink === ''"
                        type="button" 
                        class="bouton" 
                        @click="addCustomLink"
                        :title="messages.get('common', 'submit')"
                        >
                        <i class="fa fa-plus" aria-hidden="true"></i>
                    </button>
                </div>
            </fieldset>
        </div>
        <div 
            v-if="widget.widgetSettings.menu && widget.widgetSettings.menu.length || 
                  widget.widgetSettings.menuCustom && widget.widgetSettings.menuCustom.length" 
            class="mb-s">
            
            <label class="widget-form-label" role="presentation">{{ messages.get('dashboard', 'form_active_menu_list') }}</label>
            <ul class="widget-menu-list">
                <li v-for="menu, index in widget.widgetSettings.menu"  :key="index" >
                    <span>{{ menu.label}}</span>

                    <div class="widget-menu-left">
                        <input 
                            type="color" 
                            :id="'widget-color' + index"
                            v-model="menu.backgroundColor">
                        <button 
                            type="button" 
                            class="dashboard-button" 
                            @click="removeMenu(index)"
                            :title="messages.get('common', 'remove')"
                            >
                            <i class="fa fa-trash" aria-hidden="true"></i>
                        </button>
                    </div>
                </li>
                <li v-for="menuCustom, index in widget.widgetSettings.menuCustom"  :key="'custom' + index" >
                    <span>{{ menuCustom.label}}</span>

                    <div class="widget-menu-left">
                        <input 
                            type="color" 
                            :id="'widget-color' + index"
                            v-model="menuCustom.backgroundColor">
                        <button 
                            type="button" 
                            class="dashboard-button" 
                            @click="removeMenuCustom(index)"
                            :title="messages.get('common', 'remove')"
                            >
                            <i class="fa fa-trash" aria-hidden="true"></i>
                        </button>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>

import formDisplay from "../common/formDisplay.vue";
export default {
    props: ["widget", "from", "current_user", "widget_type"],
    components: {
        formDisplay
    },
    data: function(){
        return {
            configuration:null,
            selectedMenu: [],
            customLink: "",
            customLabel: "",
            defaultColor: "#444444" 
        }
    },
    mounted: async function () {
        if(this.widget.widgetSettings) {
            if(!this.widget.widgetSettings.menu) {
                this.$set(this.widget.widgetSettings, 'menu', []);
            }
            if(!this.widget.widgetSettings.menuCustom) {
                this.$set(this.widget.widgetSettings, 'menuCustom', []);
            }
        }

        this.widget.widgetSettings.menu.forEach(menu => {
            this.selectedMenu.push(menu.hash);
        });

        this.configuration = await this.getConfiguration();

    },
    computed: {
        settings: function() {
            if(this.from == 'widget') {
                return this.widget.widgetSettings;
            }

            return this.widget.dashboardWidgetSettings;
        }
    },
    methods: {
        getConfiguration: async function () {
            let response = await this.ws.post('widget', 'getConfiguration', { source: this.widget_type.source });

            if (response.error) {
                this.notif.error(this.messages.get('dashboard', response.errorMessage));
                return;
            }

            return response;
        },
        addMenu: function(){
            let menuList = [];

            for(let hash of this.selectedMenu) {
                for(let menu of this.configuration) {
                    if(menu.hash === hash) {
                        menuList.push(menu);
                        break;
                    }

                    
                    let findTab = null;
                    for(let sectionKey of Object.keys(menu.sections)) {
                        for(let tab of menu.sections[sectionKey].tabs) {
                            if(tab.hash === hash) {
                                findTab = tab;
                                break;
                            }
                        }
                    }

                    if(findTab) {
                        menuList.push(findTab);
                        break;
                    }
                }
            }

            for(let tempMenu of menuList) {
                let found = false;

                for(let menu of this.widget.widgetSettings.menu) {
                    if(menu.hash === tempMenu.hash) {
                        found = true;

                        if(!menu.backgroundColor) {
                            tempMenu.backgroundColor = this.defaultColor;
                            continue;
                        }

                        tempMenu.backgroundColor = menu.backgroundColor;
                    }
                }

                if(!found) {
                    tempMenu.backgroundColor = this.defaultColor;
                }
            }

            this.$nextTick(() => {
                this.$set(this.widget.widgetSettings, 'menu', menuList);
            });
        },
        addCustomLink: function() {
            this.widget.widgetSettings.menuCustom.push({
                label: this.customLabel,
                link: this.customLink,
                backgroundColor: this.defaultColor
            })

            this.customLabel = '';
            this.customLink = '';
        },
        removeMenu: function(index){
            let menu = this.widget.widgetSettings.menu[index];
            this.selectedMenu.splice(this.selectedMenu.indexOf(menu.hash), 1)
            this.widget.widgetSettings.menu.splice(index, 1);

        },
        removeMenuCustom : function(index){
            this.widget.widgetSettings.menuCustom.splice(index, 1);
        },
        checkForm: function() {
            if(!this.widget.widgetSettings.menu.length && !this.widget.widgetSettings.menuCustom.length) {
                this.notif.error(this.messages.get('dashboard', 'form_menu_error_widget'));
                return false;
            }

            return true;
        }
    },
}
</script>