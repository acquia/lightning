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
