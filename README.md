# Gorgo Email Debug Bundle

```
  gorgo:debug:email:template                        Displays current email templates for an application
  gorgo:debug:email:template:compile                Renders given email template
  gorgo:debug:email:variable                        Displays current email template variables for an application
  gorgo:email:template:export                       Exports email templates
  gorgo:email:template:import                       Imports email templates
```


### Examples
Uses SMPT Settings and sends an email
```
php app/console gorgo:debug:email:template:compile --template order_confirmation_email --entity-id=16 --recipient=admin@example.com
```

```
Message successfully send to "admin@example.com"
```

Outputs EMAIL content to stdout
```
php app/console gorgo:debug:email:template:compile --template order_confirmation_email --entity-id=16
```

Displays list of available templates
```
php app/console gorgo:debug:email:template
```
```
+----+-----------------------------------------+--------------------------------------------------+------+--------+---------+----------+--------+
| ID | NAME                                    | ENTITY CLASS                                     | TYPE | SYSTEM | VISIBLE | EDITABLE | PARENT |
+----+-----------------------------------------+--------------------------------------------------+------+--------+---------+----------+--------+
| 1  | force_reset_password                    | Oro\Bundle\UserBundle\Entity\User                | html | Yes    | Yes     | Yes      | N/A    |
| 2  | user_reset_password                     | Oro\Bundle\UserBundle\Entity\User                | html | Yes    | Yes     | Yes      | N/A    |
| 3  | user_reset_password_as_admin            | Oro\Bundle\UserBundle\Entity\User                | html | Yes    | Yes     | Yes      | N/A    |
| 4  | user_change_password                    | Oro\Bundle\UserBundle\Entity\User                | html | Yes    | Yes     | Yes      | N/A    |
......
| 99 | order_confirmation_email                | Oro\Bundle\OrderBundle\Entity\Order              | html | Yes    | Yes     | Yes      | N/A    |
+----+-----------------------------------------+--------------------------------------------------+------+--------+---------+----------+--------+
```

Displays info for specific template
```
php app/console gorgo:debug:email:template --template order_confirmation_email
```

```
@name = order_confirmation_email
@entityName = Oro\Bundle\OrderBundle\Entity\Order
@subject = Your order has been received.
@isSystem = 1
@isEditable = 1

{%  extends 'base.html.twig' %}

{% block content %}
...
{% endblock %}
```


Displays System-wide variables
```
php app/console gorgo:debug:email:variable 
```

```
+--------------------+-----------------+--------+-----------------------------------------+
| Name               | Title           | Type   | Value                                   |
+--------------------+-----------------+--------+-----------------------------------------+
| system.appURL      | Application URL | string | https://dev.gorgo.in                  |
| system.currentDate | Current date    | string | May 32, 2018                            |
| system.currentTime | Current time    | string | 12:03 PM                                |
+--------------------+-----------------+--------+-----------------------------------------+
```

Displays Class-based variables
```
php app/console gorgo:debug:email:variable --entity-class="Oro\Bundle\OrderBundle\Entity\Order"
```

```
Entity Variables
+-------------------------------------+----------------------------------------------------------+-----------+
| Name                                | Title                                                    | Type      |
+-------------------------------------+----------------------------------------------------------+-----------+
| entity.acContactCount               | Total times contacted                                    | integer   |
....
| entity.url.create                   | Entity Create Page                                       | string    |
| entity.url.index                    | Entity Grid Page                                         | string    |
| entity.url.update                   | Entity Update Page                                       | string    |
| entity.url.view                     | Entity View Page                                         | string    |
+-------------------------------------+----------------------------------------------------------+-----------+
```

Displays Entity-based variables
```
php app/console gorgo:debug:email:variable --entity-class="Oro\Bundle\OrderBundle\Entity\Order" --entity-id=16
```

```
Entity Variables
+-------------------------------------+----------------------------------------------------------+-----------+---------------------------------------------------------------+
| Name                                | Title                                                    | Type      | Value                                                         |
+-------------------------------------+----------------------------------------------------------+-----------+---------------------------------------------------------------+
| entity.acContactCount               | Total times contacted                                    | integer   |                                                               |
....
| entity.url.create                   | Entity Create Page                                       | string    | https://dev.gorgo.in/admin/order/create                       |
| entity.url.index                    | Entity Grid Page                                         | string    | https://dev.gorgo.in/admin/order/                             |
| entity.url.update                   | Entity Update Page                                       | string    | https://dev.gorgo.in/admin/order/update/16                    |
| entity.url.view                     | Entity View Page                                         | string    | https://dev.gorgo.in/admin/order/view/16                      |
+-------------------------------------+----------------------------------------------------------+-----------+---------------------------------------------------------------+
```


Export all Email templates
```
php app/console gorgo:email:template:export D:\temp
```

```
Found 99 templates for export
```

Export Specific email template
```
php app/console gorgo:email:template:export --template order_confirmation_email D:\temp 
```

```
Found 1 templates for export
```

Import Specific email template
```
php app/console gorgo:email:template:import D:\temp\order_confirmation_email.html.twig --force 
```

Bundle Import email templates from folder
```
php app/console gorgo:email:template:import D:\temp --force 
```

```
Found 999 templates
"authentication_code" updated
...
"order_confirmation_email" updated
```

