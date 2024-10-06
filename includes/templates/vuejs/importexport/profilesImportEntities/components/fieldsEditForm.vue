<template>
    <div class="form-contenu">
        <div class="row ie-row">
            <div class="colonne25">
                <b>
                    {{ messages.get('importexport', 'ie_field_name') }}
                </b>
            </div>
            <div class="colonne75">
                {{ field.fieldLabel }}
            </div>
        </div>
        <div class="row ie-row">
        	<div class="colonne25">
                <label class="etiquette" for="sourceName">
                    {{ messages.get('importexport', 'ie_field_function') }}
                </label>
            </div>
            <div class="colonne75">
            	<select id="field_functions" v-model="dblFunction.functionName">
					<option value='' disabled>{{ messages.get('importexport', 'ie_fields_empty_value') }}</option>
					<option v-for='(entityTypeFieldFunction, index) in entityTypeFieldFunctions' :value='entityTypeFieldFunction.value' :key="index">{{ entityTypeFieldFunction.label }}</option>
				</select>
				<button type='button' class='bouton' @click='addDblFunction' :disabled='dblFunction.functionName == ""'>
					<i class="fa fa-plus" aria-hidden="true"></i>
				</button>
            </div>
		</div>
		<div>
			<table class="uk-table uk-table-small uk-table-striped uk-table-middle">
				<caption>{{ messages.get('importexport', 'ie_fields_functions_list') }}</caption>
				<thead>
					<tr>
						<th>
							{{ messages.get('importexport', 'ie_field_function') }}
						</th>
						<th>
							{{ messages.get('importexport', 'ie_action') }}
						</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="(dblFunction, index) in field.functions" :key="index">
						<td>{{ getDblFunctionLabel(dblFunction) }}</td>
						<td>
							<button type="button" class="bouton" @click="remove(dblFunction)">{{ messages.get('common', 'remove') }}</button>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
        <div>
            <button type="button" class="bouton" @click="cancel">{{ messages.get('common', 'cancel') }}</button>
            <button type="button" class="bouton" @click="save(field)">{{ messages.get('common', 'common_submit') }}</button>
        </div>
    </div>
</template>

<script>

export default {
    components: {
    	
    },
    props : {
        field : {
            'type' : Object
        }

    },
    created: function() {

    },
    computed : {

    },
    data: function() {
		return {
			dblFunction : {
				functionName : '',
				functionLabel : ''
			}
		}
    },
    methods:{
    	cancel : function() {
    		this.$emit('cancel', this.field);
    	},
        save : function()
        {
            this.$emit('save', this.field);
        },
        addDblFunction: async function() {
        	const dblFunction = Object.assign({}, this.dblFunction);
    		this.field.functions.push(dblFunction);
			this.dblFunction = this.getEmptyDblFunction();
        },
		getDblFunctionLabel: function(functionName) {
        	let dblFunction = this.entityTypeFieldFunctions.find(element => element.value == functionName);
        	if(dblFunction && dblFunction.label) {
				return dblFunction.label;
			}
        	return "";
        },
        getEmptyDblFunction: function() {
        	return {
				functionName : '',
				functionLabel : ''
			};
        }
    },
}
</script>
