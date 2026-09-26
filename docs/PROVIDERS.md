# Services externes

Twende Market termine les parcours dans l’application. Lorsqu’un prestataire réel n’est pas configuré, l’interface, les validations et les écritures en base fonctionnent, et l’écran le dit.

| Fonctionnalité | Prestataire | Variable | État |
| --- | --- | --- | --- |
| Paiement test | Aucun débit | `PAYMENT_DRIVER=sandbox` | Actif. Confirme la commande, crédite le portefeuille vendeur, n’appelle aucun réseau de paiement. |
| Paiement à la livraison | Aucun | méthode `cod` | Actif. Le paiement passe à payé quand le livreur confirme la remise. |
| Mobile money / carte | Non branché | — | Non proposé dans le checkout. Ne pas présenter le mode test comme un paiement réel. |
| SMS OTP | Journal | `SMS_DRIVER=log` | Le code est journalisé, pas envoyé à un opérateur. |
| Recherche image | Analyse locale de couleur | `VISION_DRIVER=local` | Active et annoncée comme limitée. |
| OpenAI Vision | OpenAI | `OPENAI_API_KEY`, `OPENAI_VISION_MODEL` | Utilisé seulement si la clé répond. Sinon repli local. |
| Google Vision | Google | — | Non implémenté. Aucun libellé inventé. |
| AWS Rekognition | AWS | — | Non implémenté. Aucun libellé inventé. |
| Recherche catalogue | Base de données | `SEARCH_DRIVER=database` | Active. |
| E-mail | Mailer Laravel | `MAIL_MAILER` | Les vues d’e-mail utilisent le logo officiel. En local, le mailer peut être `log` ou `array`. |

La commission (`TWENDE_COMMISSION_PERCENT`, défaut 10) est retenue sur le vendeur. Elle n’est pas ajoutée au total du client. `TWENDE_TAX_PERCENT` vaut 0 : aucune taxe n’est inventée.
