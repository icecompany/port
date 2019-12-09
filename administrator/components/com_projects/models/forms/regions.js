var url = "/index.php?option=com_projects&task=api.getCities&api_key=4n98tpw49vtpw496npyww9p6by";
window.onload = function () {
    let select_elem = jQuery("#jform_regID");
    let select_elem_fact = jQuery("#jform_regID_fact");
    select_elem.chosen();
    select_elem_fact.chosen();
    let f = document.querySelector("#jform_regID_chzn .chzn-drop .chzn-search input");
    let ff = document.querySelector("#jform_regID_fact_chzn .chzn-drop .chzn-search input");
    unlockSearchCity(f);
    unlockSearchCity(ff);
    loadCity();
    jQuery(f).autocomplete({source: function () {
            let val = f.value;
            if (val.length < 2) return;
            jQuery.getJSON(`${url}&q=${val}`, function (json) {
                select_elem.empty();
                jQuery.each(json, function (idx, obj) {
                    select_elem.append(`<option value="${obj.id}">${obj.name} (${obj.region})</option>`);
                });
                select_elem.chosen({width: "95%"});
                select_elem.trigger("liszt:updated");
                unlockSearchCity(f);
                f.value = val;
            });
        }
    });
    jQuery(ff).autocomplete({source: function () {
            let val = ff.value;
            if (val.length < 2) return;
            jQuery.getJSON(`${url}&q=${val}`, function (json) {
                select_elem_fact.empty();
                jQuery.each(json, function (idx, obj) {
                    select_elem_fact.append(`<option value="${obj.id}">${obj.name} (${obj.region})</option>`);
                });
                select_elem_fact.chosen({width: "95%"});
                select_elem_fact.trigger("liszt:updated");
                unlockSearchCity(ff);
                ff.value = val;
            });
        }
    });
};

function unlockSearchCity(f) {
    let chzn = document.querySelector("#jform_regID_chzn");
    chzn.classList.remove("chzn-container-single-nosearch");
    let chzn_fact = document.querySelector("#jform_regID_fact_chzn");
    chzn_fact.classList.remove("chzn-container-single-nosearch");
    f.removeAttribute('readonly');
}

function loadCity() {
    let id = document.querySelector("#jform_hidden_city_id").value;
    let title = document.querySelector("#jform_hidden_city_title").value;
    let id_fact = document.querySelector("#jform_hidden_city_fact_id").value;
    let title_fact = document.querySelector("#jform_hidden_city_fact_title").value;
    if (id !== undefined && title !== undefined) {
        let f = document.querySelector("#jform_regID_chzn .chzn-drop .chzn-search input");
        let ff = document.querySelector("#jform_regID_fact_chzn .chzn-drop .chzn-search input");
        let select_elem = jQuery("#jform_regID");
        let select_elem_fact = jQuery("#jform_regID_fact");
        select_elem.append(`<option value="${id}">${title}</option>`);
        select_elem.chosen({width: "95%"});
        select_elem.trigger("liszt:updated");
        select_elem_fact.append(`<option value="${id_fact}">${title_fact}</option>`);
        select_elem_fact.chosen({width: "95%"});
        select_elem_fact.trigger("liszt:updated");
        unlockSearchCity(f);
        unlockSearchCity(ff);
    }
}