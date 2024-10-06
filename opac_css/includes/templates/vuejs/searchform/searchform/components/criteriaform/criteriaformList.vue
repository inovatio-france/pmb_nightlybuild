<template>
	<div class="rmc_criteria_form_list">
		<label :for="'op_' + index + '_' + criteria.FIELD_ID " class="visually-hidden">{{ pmb.getMessage('searchform', 'operatorListLabel') }}</label>
		<operators v-if="criteria.QUERIES" 
			:fieldId="criteria.FIELD_ID" 
			:index="index" 
			:queries="criteria.QUERIES" 
			:selected="selectedOp" >
		</operators>
		<label :for="'rmc_label_list_' + index" class="visually-hidden">{{ pmb.getMessage('searchform', 'listLabel') }}</label>
		<select :id="'rmc_label_list_' + index" :value="searchValue" :name="name">
			<option v-for="(item, order) in criteria.INPUT_OPTIONS.VALUES" :key="order" :value="item.id">{{item.value}}</option>
		</select>
		<fieldvars v-if="showfieldvars" :fields="criteria.VAR" :field="criteria_id" :index="index"></fieldvars>	
	</div>
</template>


<script>
import operators from "./operators.vue";
import fieldvars from "./fieldvars.vue";

export default {
	name : "criteriaFormList",
	props : ['criteria', 'index', 'searchData', 'showfieldvars'],
	data : function(){
		return {
			selectedValues : []
		}
	},
	components : {
	    operators,
	    fieldvars
	},
	computed : {
		name: function() {
			return `field_${this.index}_${this.criteria.FIELD_ID}[]`;
		},
        selectedOp: function() {
        	if(this.searchData[this.index] && this.searchData[this.index].OP){
	            for (var i = 0; i < this.criteria.QUERIES.length; i++) {
	                var operator = this.criteria.QUERIES[i];
	                if (this.searchData[this.index].OP == operator['OPERATOR']) {
		        		return this.searchData[this.index].OP;
	                }
	            }
        	}
            return this.criteria.OPERATOR[0];
        },
        searchValue: function() {
        	if(this.searchData[this.index] && this.searchData[this.index].FIELD){
        		return this.searchData[this.index].FIELD[0];
        	}
            return "";
        }
	}
}
</script>