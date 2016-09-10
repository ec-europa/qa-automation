# @todo: MULTISITE-XXXXX

On rare occasions the QA team allows developers to push the refractoring of a piece
of code to the next release. Such a request is taken seriously by the QA team and is
discussed thoroughly before allowing this.

But when allowed the QA team requires the developer to add an @todo comment in their
code with a reference to the ticket where we explicitly give written permission to
keep the current code until the next release. Serious performance and security issues
will never be postponed!

Each release we scan your project for these tags and will require the developer to
refractor the mentioned code if there is a @todo tag found in the codebase. We also
scan the diff of the pull request for the removal of any tags and will check if the
mentioned code has been properly refractored.
