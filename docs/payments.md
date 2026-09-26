# Paiements Twende Market

Le frontend ne confirme jamais un paiement. `PaymentService` enregistre une intention, puis un adaptateur décide du statut.

| Moyen | Quand il apparaît | Statut après commande |
| --- | --- | --- |
| Paiement à la livraison | toujours | `unpaid` jusqu'à la livraison |
| Paiement test | `PAYMENT_DRIVER=sandbox` | `paid` après contrôle serveur du montant, sans débit réel |
| M-Pesa, Airtel Money, Orange Money, Afrimoney, carte | activé et clé + secret renseignés | `pending` jusqu'au webhook |

Variables :

```text
PAYMENT_DRIVER=sandbox
PAYMENT_WEBHOOK_SECRET=
PAYMENT_MPESA_ENABLED=false
PAYMENT_MPESA_KEY=
PAYMENT_MPESA_SECRET=
PAYMENT_AIRTEL_ENABLED=false
PAYMENT_AIRTEL_KEY=
PAYMENT_AIRTEL_SECRET=
PAYMENT_ORANGE_ENABLED=false
PAYMENT_ORANGE_KEY=
PAYMENT_ORANGE_SECRET=
PAYMENT_AFRIMONEY_ENABLED=false
PAYMENT_AFRIMONEY_KEY=
PAYMENT_AFRIMONEY_SECRET=
PAYMENT_CARD_ENABLED=false
PAYMENT_CARD_KEY=
PAYMENT_CARD_SECRET=
```

Le webhook `POST /webhooks/payments/{provider}` exige l'en-tête `X-Twende-Signature` : HMAC SHA-256 du corps brut avec `PAYMENT_WEBHOOK_SECRET`. Le corps JSON contient `reference`, `amount` (entier, unité minimale), `currency` et `status`. Le montant, la devise et la référence doivent correspondre au paiement. Un second appel identique ne crédite pas le portefeuille deux fois.

Sans secret, ou sans identifiants fournisseur, aucun débit live n'est tenté.

L'abonnement boutique est de 3,00 USD. Le montant CDF vient de la dernière ligne `exchange_rates` (unité minimale de CDF pour 1 USD), pas d'un taux écrit dans le code. Les points se convertissent avec `points_per_usd` (nombre de points pour 1 USD), modifiable dans les réglages admin.
