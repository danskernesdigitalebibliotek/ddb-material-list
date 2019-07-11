Loose notes that should be rewritten to proper documentation
------------------------------------------------------------

Behot is used for behavior tests. Features in tests/features, contexts
in tests/contexts.

MaterialListContext reboots the app on each scenario. It calls the
controller directly with the appropriate request.

Behat generates code coverage when running tests. To view them,
generate an HTML report with `./vendor/bin/phpcov merge --html=./coverage/html ./coverage`

Remember to add --strict to the behat invocation in main.workflow when
all the existing features has been implemented. Then behat will fail
on unimplemented features tests going forward.
