## Where are the controllers?

Where are the controllers? - you might think.

The reason why they are absent is that the API version handling
was introduced _after_ version 1.0.0 already was released.

But since the VersionSwitcher middleware
(which was introduced with the API version handling)
falls back to the controllers configured in the routes in web.php,
there is no problem.
