<template>
	<div class="rmc_criteria_form_authority">
		<input v-if="operatorSelected != 'AUTHORITY'" :id="id + '_id_0'" :name="id + '[]'"  type="hidden" v-model="searchValue">
		<input v-else :id="id + '_id_0'" :name="id + '[]'" type="hidden" :value="searchValueId">
		
		<div :id="'d' + id + '_lib_' + index" class="ajax_completion" ></div>

		<label :for="opName" class="visually-hidden">{{ pmb.getMessage('searchform', 'operatorAuthorityLabel') }}</label>
		<select :id="opName" :name="opName" class="rmc_search_op" v-model="operatorSelected">
			<option v-for="(operator, key) in operators" :key="key" :value="operator.value">
				{{ operator.label }}
			</option>
		</select>
		<label :for="id + '_lib_' + index" class="visually-hidden">{{ pmb.getMessage('searchform', 'searchLabel') }}</label>
		<div class="rmc_search_authority_container">
			<input
				:id="id + '_lib_' + index"
				:name="name"
				class="rmc_search_authority rmc_search_txt"
				type="text"
				autocomplete="off"
				:autfield="autfield"
				:autid="autid"
				:list="id + '_lib_' + index + '_datalist'"
				v-model="searchValue"
				@input.prevent="updateDataList"
				@keydown.down.prevent="increaseIndex"
				@keydown.up.prevent="decreaseIndex"
				@keydown.tab.exact.prevent="increaseIndex"
				@keydown.shift.tab.prevent="decreaseIndex"
				@keydown.esc="hideDatalist(true)"
				@keydown.enter="handleEnter"
				@blur="hideDatalist(true)">
	
			<ul v-if="dataListDisplayed" :id="id + '_lib_' + index + '_datalist'" class="rmc_datalist" role="listbox">
				<li v-for="(element, index) in dataList" :key="index"
					:class="`rmc_datalist_option ${index == dataListIndex ? 'rmc_datalist_option_active' : ''}`" 
					:data-entity_id="element.value"
					@click.self="selectElement(index); hideDatalist(false)"
					:aria-selected="index == dataListIndex"
					role="option">
	
					<div 
						:title="element.label" 
						class="rmc_datalist_label"
						@click.self="selectElement(index); hideDatalist(false)">
	
						{{ element.label }}
					</div>
				</li>
			</ul>
		</div>

        <fieldvars :fields="criteria.VAR" :fieldId="criteria.FIELD_ID" :index="index" />
    </div>
</template>
<script>
import fieldvars from "./fieldvars.vue";

export default {
	name: "criteriaFormAuthority",
	props : ['criteria', 'searchData', 'index', 'showfieldvars'],
	data: function () {
		return {
			selectorValue: "",
			searchValue: "",
			dataList: [],
			dataListIndex: -1,
			dataListDisplayed: false,
			operatorSelected: 'AUTHORITY',
	        searchValueId: ""
		}
	},
	components : {
	    fieldvars,
	},
	created : function() {
    	if(this.searchData[this.index] && this.searchData[this.index].OP){
            for (var i = 0; i < this.criteria.QUERIES.length; i++) {
                var operator = this.criteria.QUERIES[i];
                if (this.searchData[this.index].OP == operator['OPERATOR']) {
                	this.operatorSelected = this.searchData[this.index].OP;
                }
            }
    	}
    	
       	if(this.searchData[this.index] && this.searchData[this.index].FIELD){
	       	if(this.searchData[this.index] && this.searchData[this.index].FIELDLIB){
	       		this.searchValue = this.searchData[this.index].FIELDLIB[0];
	       	} else {
	       		this.searchValue = this.searchData[this.index].FIELD[0];
	       	}
	       	if(this.operatorSelected == 'AUTHORITY'){
	       		this.searchValueId = this.searchData[this.index].FIELD[0];
	       	}
       	}

		this.initListeners();
       	
	},
	computed: {
        name: function() {
            return `field_${this.index}_${this.criteria.FIELD_ID}_lib[]`;
        },
        autfield: function() {
        	return `field_${this.index}_${this.criteria.FIELD_ID}_id_0`;
        },
        autid: function() {
        	return `field_${this.index}_${this.criteria.FIELD_ID}_id_0`;
        },
        id: function() {
        	return `field_${this.index}_${this.criteria.FIELD_ID}`;
        },
        opName: function() {
        	return `op_${this.index}_${this.criteria.FIELD_ID}`;
        },
        operators: function() {
	        var operators = new Array();
	        if (this.criteria.QUERIES && this.criteria.QUERIES.length) {
	            for (var i = 0; i < this.criteria.QUERIES.length; i++) {
	                var operator = this.criteria.QUERIES[i];
	                if (operator) {
		                operators.push({value: operator['OPERATOR'], label: operator['LABEL']});
	                }
	            }
	        }
	        return operators;
	    },
    },
	mounted: function() {
		this.authoritiesAjaxParse(this.criteria.INPUT_TYPE);
	},
	methods: {
		authoritiesAjaxParse: function() {
			ajax_parse_dom();
		},
		selectElement(index = -1) {
			if(index != -1) {
				this.dataListIndex = index;
			}

			if(this.dataList[this.dataListIndex] && this.dataList[this.dataListIndex].value){
				this.$set(this, "operatorSelected", 'AUTHORITY');
				this.$set(this, "searchValueId", this.dataList[this.dataListIndex].value);
				this.$set(this, "searchValue", this.dataList[this.dataListIndex].label);
			}
		},
		updateDataList: function() {
			if(this.operatorSelected != 'AUTHORITY'){
				return;
			}

			var formData = new FormData();
			formData.append("handleAs", "json");
			formData.append("completion", this.criteria.INPUT_OPTIONS.AJAX);
			formData.append("autexclude", "");
			formData.append("param1", "");
			formData.append("param2", 1);
			formData.append("rmc_responsive", 1);
			
			var data = this.searchValue;
			if (!data) {
				data = "*";
			}
			formData.append("datas", data);
			
			fetch("./ajax_selector.php", {
				method: 'POST',
				body: formData
			}).then((response)=> {
				if (response.ok) {
					response.json().then((result)=> {
						this.setDatalist(result);
				    });
				} else {
					console.error("Error search special ajax");
				}
			}).catch((error) => {
				console.error(error.message);
			});
		},
		setDatalist: function(data) {
			this.dataList = data;

			if(this.dataList.length > 0) {
				this.displayDatalist();
				return;
			}

			this.hideDatalist();
		},
		initListeners : function() {
			this.$root.$on("beforeSubmit", () => {
				let input = document.getElementById(this.id+'_id_0');
				if(input != null) {
					if(input.value == ""){
						//Si on n'a pas recupere l'id de l'autorite
						this.operatorSelected = "BOOLEAN";
					}
				}
			})
		},
		increaseIndex : function() {
			if(this.dataList.length == 0 || !this.dataListDisplayed) {
				this.updateDataList();
				return;
			}

			if(this.dataListIndex + 1 >= this.dataList.length) {
				return;
			}

			this.displayDatalist();

			this.dataListIndex++;
			if(typeof this.dataList[this.dataListIndex] == "undefined") {
				this.dataListIndex = 0;
			}

			this.updateFocus();
			this.selectElement();
		},
		decreaseIndex: function() {
			if(this.dataList.length == 0 || this.dataListIndex == 0 || !this.dataListDisplayed) {
				return;
			}
			this.dataListIndex--;
			if(typeof this.dataList[this.dataListIndex] == "undefined") {
				this.dataListIndex = 0;
			}

			this.updateFocus();
			this.selectElement();
		},
		resetIndex: function() {
			this.dataListIndex = -1;
		},
		displayDatalist: function() {
			this.dataListDisplayed = true;
		},
		handleEnter: function(event) {
			if(!this.dataListDisplayed && this.dataListIndex == -1) {
				return;
			}

			event.preventDefault();

			this.selectElement();
			this.hideDatalist();
		},
		hideDatalist: function(cooldown = false) {
			if(cooldown) {
				//On attend un peu que le click soit pris en compte
				setTimeout(() => this.dataListDisplayed = false, 140)
			} else {
				this.dataListDisplayed = false
			}

			this.resetIndex();
		},
		updateFocus: function() {
			if(!this.dataListDisplayed) {
				return;
			}

			const customDatalist = document.querySelector('ul.rmc_datalist');
  			const listItems = customDatalist.querySelectorAll('li.rmc_datalist_option');

			for (let i = 0; i < listItems.length; i++) {
				if (i === this.dataListIndex) {
					listItems[i].classList.add('focused');
					listItems[i].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
				} else {
					listItems[i].classList.remove('focused');
				}
			}
		}
	}
}
</script>