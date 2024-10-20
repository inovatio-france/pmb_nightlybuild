<template>
	<span :class="'search_variable_'+field.NAME">
		<span :class="field.SPAN">
			<label :for="id">{{ field.COMMENT }}</label>
		</span>
		<span class="search_value">
			<select :id="id" :name="name">
				<option v-for="(option, key) in field.OPTIONS" v-if="isNumeric(key)" :key="key" :value="option.VALUE">
					{{ option.LABEL }}
				</option>
			</select>
		</span>
	</span>
</template>

<script>
export default {
	name: "fieldvarsFormQueryList",
	props : ['field', 'id', 'name'],
	data : function(){
		return {
			dataSelected : 0,
		}
	},
	watch:{
		selected: function(val) {
			this.dataSelected = val;
		}
	},
	computed: {
	},
	methods: {
		isNumeric : function(key) {
			return !isNaN(key);
		},
	}
}
</script>