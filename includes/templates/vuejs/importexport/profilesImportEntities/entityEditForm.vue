<template>
    <div class="form-contenu">
    	<h3>{{ messages.get('importexport', 'ie_profile_import_entity_settings') }}</h3>
		<div class='row ie-row'>
			<input id="activated" type="checkbox" class="switch" value="1" v-model="entity.entitySettings.activated"/>
			<label for="activated">{{ messages.get('importexport', 'ie_profile_import_entity_activated') }}</label>
		</div>
		<div class='row ie-row'>
			<fieldset class="ie-fieldset">
				<legend>{{ messages.get('importexport', 'ie_fields_doublon') }}</legend>
				<fields-doublon-list mode="Doublon" :fields="entity.entitySettings.doublon.fields" :entity-type-fields="entityTypeFields" :id-profile="idProfile">
				</fields-doublon-list>
			</fieldset>
			<fieldset class="ie-fieldset">
				<legend>{{ messages.get('importexport', 'ie_fields_creation') }}</legend>
				<fields-list mode="Creation" :fields="entity.entitySettings.creation.fields" :entity-type-fields="entityTypeFields" :id-profile="idProfile">
				</fields-list>
			</fieldset>
			<fieldset class="ie-fieldset">
				<legend>{{ messages.get('importexport', 'ie_fields_replacement') }}</legend>
				<fields-list mode="Replacement" :fields="entity.entitySettings.replacement.fields" :entity-type-fields="entityTypeFields" :id-profile="idProfile">
				</fields-list>
			</fieldset>
		</div>
    </div>
</template>

<script>
import fieldsDoublonList from "./components/fieldsDoublonList.vue";
import fieldsList from "./components/fieldsList.vue";

export default {
    components: {
    	fieldsDoublonList,
    	fieldsList,
    },
    props : {
        entity : {
            'type' : Object
        },
        entityTypeFields : {
			'type' : Array
		},
		idProfile : {
			'type' : Number
		}

    },
    created: function() {
        this.initEntitySettings();
    },
    computed : {

    },
    methods:{
    	initEntitySettings : function () {
            if(! this.entity.entitySettings){
                this.entity.entitySettings = {};
            }
            if(! this.entity.entitySettings.creation){
                this.$set(this.entity.entitySettings, 'creation', {});
            }
            if(! this.entity.entitySettings.creation.fields){
                this.$set(this.entity.entitySettings.creation, 'fields', []);
            }
            if(! this.entity.entitySettings.replacement){
                this.$set(this.entity.entitySettings, 'replacement', {});
            }
            if(! this.entity.entitySettings.replacement.fields){
                this.$set(this.entity.entitySettings.replacement, 'fields', []);
            }
            if(! this.entity.entitySettings.doublon){
                this.$set(this.entity.entitySettings, 'doublon', {});
            }
            if(! this.entity.entitySettings.doublon.fields){
                this.$set(this.entity.entitySettings.doublon, 'fields', []);
            }
        },
        save : function()
        {
            this.$emit('save', this.entity);
        },
        remove : function()
        {
            this.$emit('remove', this.entity.id);
        }


    },
}
</script>
