<?php

/**
 * Hook Dolibarr pour le module triggers
 * Documentation : https://wiki.dolibarr.org/index.php?title=Système_de_Hooks
 */
class ActionsTriggers
{
    /**
     * Injecte du JS dans le footer HTML
     *
     * @param   array    $parameters     Hook parameters
     * @param   Object   $object         Current object
     * @param   string   $action         Current action
     * @param   object   $hookmanager    Hook manager
     * @return  int                      0 = continue, >0 = replace standard code, <0 = error
     */
    function printCommonFooter($parameters, &$object, &$action, $hookmanager)
    {
        global $langs;

        // CONTEXTE PAGE COMMANDE ou PROPAL
        if (in_array('ordercard', explode(':', $parameters['context'])) || in_array('propalcard', explode(':', $parameters['context']))) {

?>
            <script>
                $(document).ready(function() {
                    console.log("Script hook chargé - nouvelle version (filtré sur 'create')");

                    const socid = getSocidFromInput();
                    console.log("ID société trouvé:", socid);

                    if (!socid || socid == -1) {

                        return;
                    }

                    fetchClientExtrafields(socid);

                    $(document).on("input change", 'input[name^="options_"], select[name^="options_"]', function() {
                        $(this).attr("data-modified", "true");
                    });

                    function getSocidFromInput() {
                        var socid = $("input[name='socid']").val();
                        if (!socid) socid = $("#socid").val() || $("select[name='socid']").val();
                        return socid;
                    }

                    function fetchClientExtrafields(socid) {
                        console.log("Envoi de la requête AJAX... -> socid:", socid);

                        $.ajax({
                            url: "<?php print dol_buildpath('/custom/triggers/get_client_extrafields.php', 1); ?>",
                            type: "POST",
                            data: {
                                socid: socid
                            },
                            dataType: "json",
                            success: function(data) {
                                if (data.success) {
                                    for (let key in data.extrafields) {
                                        let textValue = data.extrafields[key];

                                        let select = $('#options_' + key);
                                        if (select.length === 0) {
                                            select = $('select[name="options_' + key + '"]');
                                        }

                                        let select2Span = $('#select2-options_' + key + '-container');
                                        let input = $('input[name="options_' + key + '"]');

                                        if (select.length > 0 && !select.attr("data-modified")) {
                                            let valueExists = false;
                                            select.find('option').each(function() {
                                                if ($(this).val() === textValue || $(this).text() === textValue) {
                                                    valueExists = true;
                                                    return false;
                                                }
                                            });

                                            if (valueExists) {
                                                select.val(textValue).trigger("change");
                                            } else {
                                                select.find('option').each(function() {
                                                    if ($(this).text().includes(textValue)) {
                                                        select.val($(this).val()).trigger("change");
                                                        return false;
                                                    }
                                                });
                                            }
                                        }

                                        if (select2Span.length > 0) {
                                            select2Span.text(textValue).attr("title", textValue);
                                        }

                                        if (input.length > 0 && !input.attr("data-modified")) {
                                            input.val(textValue).trigger("change");
                                        }
                                    }

                                    activateExtrafieldsEdition();
                                } else {
                                    console.error("Erreur AJAX :", data.error);
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("Erreur AJAX :", status, error);
                                console.log("Réponse serveur:", xhr.responseText);
                            }
                        });
                    }

                    function activateExtrafieldsEdition() {
                        $('input[name^="options_"], select[name^="options_"]').each(function() {
                            $(this).prop('disabled', false);
                            $(this).attr('title', 'Champ modifiable pour cette proposition');
                        });
                        console.log("Extrafields rendus modifiables");
                    }
                });
            </script>
<?php
        }

        return 0;
    }
}
