<?php

/**
 * Hook Dolibarr pour les propositions commerciales
 * Documentation : https://wiki.dolibarr.org/index.php?title=Système_de_Hooks
 */
class ActionsProposalTriggers
{
    function printCommonFooter($parameters, &$object, &$action, $hookmanager)
    {
        global $langs;

        if (in_array('propalcard', explode(':', $parameters['context']))) {
            print '<!-- Hook Proposal Triggers actif -->';
            print '<script>
                $(document).ready(function () {
                    console.log("Script hook proposition commerciale chargé");

                    setTimeout(function () {
                        const socid = getSocidFromInput();
                        console.log("ID société trouvé:", socid);

                        if (!socid || socid == -1) {
                      
                            return;
                        }

                        fetchClientExtrafields(socid);
                    }, 2500);

                    $(document).on("input change", \'input[name^="options_"], select[name^="options_"]\', function () {
                        $(this).attr("data-modified", "true");
                    });

                    function getSocidFromInput() {
                        var socid = $("input[name=\'socid\']").val();
                        if (!socid) {
                            socid = $("#socid").val() || $("input#socid").val() || $("select[name=\'socid\']").val();
                        }
                        return socid;
                    }

                    function fetchClientExtrafields(socid) {
                        console.log("Envoi de la requête AJAX... -> socid:", socid);

                        $.ajax({
                            url: "' . dol_buildpath('/custom/triggers/get_client_extrafields.php', 1) . '",
                            type: "POST",
                            data: { socid: socid },
                            dataType: "json",
                            success: function (data) {
                                console.log("Réponse AJAX reçue :", data);

                                if (data.success) {
                                    for (let key in data.extrafields) {
                                        let textValue = data.extrafields[key];

                                        let input = $(\'input[name="options_\' + key + \'"]\');
                                        let select2Span = $(\'#select2-options_\' + key + \'-container\');
                                        let select = $(\'select[name="options_\' + key + \'"]\');

                                        if (input.length > 0 && !input.attr("data-modified")) {
                                            input.val(textValue).trigger("change");
                                            console.log("Mise à jour du champ input :", key, "->", textValue);
                                        } else if (select2Span.length > 0 && !select.attr("data-modified")) {
                                            select2Span.text(textValue);
                                            select.val(textValue).trigger("change");
                                            console.log("Mise à jour du champ Select2 :", key, "->", textValue);
                                        } else if (select.length > 0 && !select.attr("data-modified")) {
                                            select.val(textValue).trigger("change");
                                            console.log("Mise à jour du champ select :", key, "->", textValue);
                                        } else {
                                            console.warn("Champ non trouvé :", "options_" + key);
                                        }
                                    }

                                    activateExtrafieldsEdition();
                                } else {
                                    console.error("Erreur AJAX :", data.error);
                                }
                            },
                            error: function (xhr, status, error) {
                                console.error("Erreur AJAX :", status, error);
                            }
                        });
                    }

                    function activateExtrafieldsEdition() {
                        $(\'input[name^="options_"], select[name^="options_"]\').each(function () {
                            $(this).prop(\'disabled\', false);
                            $(this).attr(\'title\', \'Champ modifiable pour cette proposition\');
                        });

                        console.log("Extrafields rendus modifiables");
                    }
                });
            </script>';
        }

        return 0;
    }
}
