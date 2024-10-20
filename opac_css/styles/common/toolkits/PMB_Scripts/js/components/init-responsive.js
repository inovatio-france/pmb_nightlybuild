$(document).ready(function () {
	$(".localisation").attr("data", function () {
		return $(".collstate_header_location_libelle").text()
	});
	$(".emplacement_libelle").attr("data", function () {
		return $(".collstate_header_emplacement_libelle").text()
	});
	$(".cote").attr("data", function () {
		return $(".collstate_header_cote").text()
	});
	$(".type_libelle").attr("data", function () {
		return $(".collstate_header_type_libelle").text()
	});
	$(".statut_opac_libelle").attr("data", function () {
		return $(".collstate_header_statut_opac_libelle").text()
	});
	$(".state_collections").attr("data", function () {
		return $(".collstate_header_state_collections").text()
	});
	$(".origine").attr("data", function () {
		return $(".collstate_header_origine").text()
	});
	$(".archive").attr("data", function () {
		return $(".collstate_header_archive").text()
	});
	$(".lacune").attr("data", function () {
		return $(".collstate_header_lacune").text()
	});
	$(".location_libelle").attr("data", function () {
		return $(".expl_header_location_libelle").text()
	});
	$(".section_libelle").attr("data", function () {
		return $(".expl_header_section_libelle").text()
	});
	$(".tdoc_libelle").attr("data", function () {
		return $(".expl_header_tdoc_libelle").text()
	});
	$(".expl_cote").attr("data", function () {
		return $(".expl_header_expl_cote").text()
	});


	$(".location_libelle").attr("data", function () {
		return $(".expl_header_location_libelle").text()
	});
	$(".section_libelle").attr("data", function () {
		return $(".expl_header_section_libelle").text()
	});
	$(".tdoc_libelle").attr("data", function () {
		return $(".expl_header_tdoc_libelle").text()
	});
	$(".expl_cote").attr("data", function () {
		return $(".expl_header_expl_cote").text()
	});

	$(".Code-barres").attr("data", function () {
		return $(".expl_header_expl_cb").text()
	});
	$(".Localisation").attr("data", function () {
		return $(".expl_header_location_libelle").text()
	});
	$(".Section").attr("data", function () {
		return $(".expl_header_section_libelle").text()
	});
	$(".Support").attr("data", function () {
		return $(".expl_header_tdoc_libelle").text()
	});
	$(".Cote").attr("data", function () {
		return $(".expl_header_expl_cote").text()
	});
	$(".Disponibilit�").attr("data", function () {
		return $(".expl_header_statut").text()
	});

	// user section
	$(".tab_empr_info_cb td+td").attr("data", function () {
		return $(".tab_empr_info_cb .etiq_champ").text()
	});
	$(".tab_empr_info_year td+td").attr("data", function () {
		return $(".tab_empr_info_year .etiq_champ").text()
	});
	$(".tab_empr_info_adh td+td").attr("data", function () {
		return $(".tab_empr_info_adh .etiq_champ").text()
	});


	// toggle facette responsice facette detection 
	if (document.getElementById('facette_wrapper')) {
		$("#tgle-facette").removeClass("uk-hidden")
	};
	
	if (window.innerWidth < 960) {
		$("#facette").addClass("uk-offcanvas");
		$("#facette_wrapper").addClass("uk-offcanvas-bar");
		$("#lvl1").hide();
		$("#main").attr("facette", "true");
	}
	
	// Specific event size
	$(window).resize(function () {
		if ($(window).width() < 960) {
			$("#facette").addClass("uk-offcanvas");
			$("#facette_wrapper").addClass("uk-offcanvas-bar");
			$("#lvl1").hide();
			$("#main").attr("facette", "true");
		} else {
			$("#facette").removeClass("uk-offcanvas");
			$("#facette_wrapper").removeClass("uk-offcanvas-bar");
			$("#lvl1").show();
			$("#main").removeAttr("facette");
		}
	});

	$('input').on('click', function () {
		$(window).off('resize');
	});
});