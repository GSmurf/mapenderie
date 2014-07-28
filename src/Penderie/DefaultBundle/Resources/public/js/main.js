// format
format_num_dossier = {'pattern': '{{9999}}-{{99}}-{{999999}}', 'persistent': true};
format_num_envoi = {'pattern': '{{9a}} {{99}} {{999}} {{9999}} {{9}}', 'persistent': true};
format_date_heure = {'pattern': '{{99}}/{{99}}/{{9999}} {{99}}:{{99}}'};

$(function() {
  $('#menu').menu();
  $('.dateHeure').formatter(format_date_heure);
  $('.numDossier').formatter(format_num_dossier);
});