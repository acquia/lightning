## Content Roles

### Creator
Creator roles are automatically created for every content type and automatically
destroyed for deleted content types. The creator roles have limited permissions
-- they can create content or edit their own. They *cannot* edit content created
by anybody else, and they cannot delete content (even their own). They can save
drafts and request review of their content, but they cannot publish it.

### Reviewer
Like creator roles, reviewer roles are also automatically created for every
content type and destroyed for deleted content types. Reviewers are intended to
*extend* creators, meaning that you will typically grant the reviewer role *and*
the creator role to users. Reviewers can edit, delete, and publish **any**
content, even content they don't own.

### Administering content roles
Content roles are configurable. They can be enabled or disabled. When disabled,
they will not be automatically created or destroyed for any content type. To
configure content roles, visit to *Manage > Configuration > System > Lightning*.

It's possible to define additional content roles and change the permissions
associated with them, but there is currently no UI for this. You'll need to alter the
`lightning_core.settings` configuration object directly, which beyond the scope of
this documentation.
