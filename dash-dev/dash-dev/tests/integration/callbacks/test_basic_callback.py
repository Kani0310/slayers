import json
from multiprocessing import Lock, Value
import pytest
import time

import dash_core_components as dcc
import dash_html_components as html
import dash_table
import dash
from dash_test_components import (
    AsyncComponent,
    CollapseComponent,
    DelayedEventComponent,
    FragmentComponent,
)
from dash.dependencies import Input, Output, State
from dash.exceptions import PreventUpdate
from dash.testing import wait


def test_cbsc001_simple_callback(dash_duo):
    lock = Lock()

    app = dash.Dash(__name__)
    app.layout = html.Div(
        [
            dcc.Input(id="input", value="initial value"),
            html.Div(html.Div([1.5, None, "string", html.Div(id="output-1")])),
        ]
    )
    call_count = Value("i", 0)

    @app.callback(Output("output-1", "children"), [Input("input", "value")])
    def update_output(value):
        with lock:
            call_count.value = call_count.value + 1
            return value

    dash_duo.start_server(app)

    assert dash_duo.find_element("#output-1").text == "initial value"
    dash_duo.percy_snapshot(name="simple-callback-initial")

    input_ = dash_duo.find_element("#input")
    dash_duo.clear_input(input_)

    for key in "hello world":
        with lock:
            input_.send_keys(key)

    wait.until(lambda: dash_duo.find_element("#output-1").text == "hello world", 2)
    dash_duo.percy_snapshot(name="simple-callback-hello-world")

    assert call_count.value == 2 + len("hello world"), "initial count + each key stroke"

    assert not dash_duo.redux_state_is_loading

    assert dash_duo.get_logs() == []


def test_cbsc002_callbacks_generating_children(dash_duo):
    """Modify the DOM tree by adding new components in the callbacks."""

    # some components don't exist in the initial render
    app = dash.Dash(__name__, suppress_callback_exceptions=True)
    app.layout = html.Div(
        [dcc.Input(id="input", value="initial value"), html.Div(id="output")]
    )

    @app.callback(Output("output", "children"), [Input("input", "value")])
    def pad_output(input):
        return html.Div(
            [
                dcc.Input(id="sub-input-1", value="sub input initial value"),
                html.Div(id="sub-output-1"),
            ]
        )

    call_count = Value("i", 0)

    @app.callback(Output("sub-output-1", "children"), [Input("sub-input-1", "value")])
    def update_input(value):
        call_count.value = call_count.value + 1
        return value

    dash_duo.start_server(app)

    dash_duo.wait_for_text_to_equal("#sub-output-1", "sub input initial value")

    assert call_count.value == 1, "called once at initial stage"

    pad_input, pad_div = dash_duo.dash_innerhtml_dom.select_one(
        "#output > div"
    ).contents

    assert (
        pad_input.attrs["value"] == "sub input initial value"
        and pad_input.attrs["id"] == "sub-input-1"
    )
    assert pad_input.name == "input"

    assert (
        pad_div.text == pad_input.attrs["value"] and pad_div.get("id") == "sub-output-1"
    ), "the sub-output-1 content reflects to sub-input-1 value"

    dash_duo.percy_snapshot(name="callback-generating-function-1")

    paths = dash_duo.redux_state_paths
    assert paths["objs"] == {}
    assert paths["strs"] == {
        "input": ["props", "children", 0],
        "output": ["props", "children", 1],
        "sub-input-1": [
            "props",
            "children",
            1,
            "props",
            "children",
            "props",
            "children",
            0,
        ],
        "sub-output-1": [
            "props",
            "children",
            1,
            "props",
            "children",
            "props",
            "children",
            1,
        ],
    }, "the paths should include these new output IDs"

    # editing the input should modify the sub output
    dash_duo.find_element("#sub-input-1").send_keys("deadbeef")

    assert (
        dash_duo.find_element("#sub-output-1").text
        == pad_input.attrs["value"] + "deadbeef"
    ), "deadbeef is added"

    # the total updates is initial one + the text input changes
    dash_duo.wait_for_text_to_equal(
        "#sub-output-1", pad_input.attrs["value"] + "deadbeef"
    )

    assert not dash_duo.redux_state_is_loading, "loadingMap is empty"

    dash_duo.percy_snapshot(name="callback-generating-function-2")
    assert dash_duo.get_logs() == [], "console is clean"


def test_cbsc003_callback_with_unloaded_async_component(dash_duo):
    app = dash.Dash()
    app.layout = html.Div(
        children=[
            dcc.Tabs(
                children=[
                    dcc.Tab(
                        children=[
                            html.Button(id="btn", children="Update Input"),
                            html.Div(id="output", children=["Hello"]),
                        ]
                    ),
                    dcc.Tab(children=dash_table.DataTable(id="other-table")),
                ]
            )
        ]
    )

    @app.callback(Output("output", "children"), [Input("btn", "n_clicks")])
    def update_out(n_clicks):
        if n_clicks is None:
            raise PreventUpdate

        return "Bye"

    dash_duo.start_server(app)

    dash_duo.wait_for_text_to_equal("#output", "Hello")
    dash_duo.find_element("#btn").click()
    dash_duo.wait_for_text_to_equal("#output", "Bye")
    assert dash_duo.get_logs() == []


def test_cbsc004_callback_using_unloaded_async_component(dash_duo):
    app = dash.Dash()
    app.layout = html.Div(
        [
            dcc.Tabs(
                [
                    dcc.Tab("boo!"),
                    dcc.Tab(
                        dash_table.DataTable(
                            id="table",
                            columns=[{"id": "a", "name": "A"}],
                            data=[{"a": "b"}],
                        )
                    ),
                ]
            ),
            html.Button("Update Input", id="btn"),
            html.Div("Hello", id="output"),
            html.Div(id="output2"),
        ]
    )

    @app.callback(
        Output("output", "children"),
        [Input("btn", "n_clicks")],
        [State("table", "data")],
    )
    def update_out(n_clicks, data):
        return json.dumps(data) + " - " + str(n_clicks)

    @app.callback(
        Output("output2", "children"),
        [Input("btn", "n_clicks")],
        [State("table", "derived_viewport_data")],
    )
    def update_out2(n_clicks, data):
        return json.dumps(data) + " - " + str(n_clicks)

    dash_duo.start_server(app)

    dash_duo.wait_for_text_to_equal("#output", '[{"a": "b"}] - None')
    dash_duo.wait_for_text_to_equal("#output2", "null - None")

    dash_duo.find_element("#btn").click()
    dash_duo.wait_for_text_to_equal("#output", '[{"a": "b"}] - 1')
    dash_duo.wait_for_text_to_equal("#output2", "null - 1")

    dash_duo.find_element(".tab:not(.tab--selected)").click()
    dash_duo.wait_for_text_to_equal("#table th", "A")
    # table props are in state so no change yet
    dash_duo.wait_for_text_to_equal("#output2", "null - 1")

    # repeat a few times, since one of the failure modes I saw during dev was
    # intermittent - but predictably so?
    for i in range(2, 10):
        expected = '[{"a": "b"}] - ' + str(i)
        dash_duo.find_element("#btn").click()
        dash_duo.wait_for_text_to_equal("#output", expected)
        # now derived props are available
        dash_duo.wait_for_text_to_equal("#output2", expected)

    assert dash_duo.get_logs() == []


def test_cbsc005_children_types(dash_duo):
    app = dash.Dash()
    app.layout = html.Div([html.Button(id="btn"), html.Div("init", id="out")])

    outputs = [
        [None, ""],
        ["a string", "a string"],
        [123, "123"],
        [123.45, "123.45"],
        [[6, 7, 8], "678"],
        [["a", "list", "of", "strings"], "alistofstrings"],
        [["strings", 2, "numbers"], "strings2numbers"],
        [["a string", html.Div("and a div")], "a string\nand a div"],
    ]

    @app.callback(Output("out", "children"), [Input("btn", "n_clicks")])
    def set_children(n):
        if n is None or n > len(outputs):
            return dash.no_update
        return outputs[n - 1][0]

    dash_duo.start_server(app)
    dash_duo.wait_for_text_to_equal("#out", "init")

    for children, text in outputs:
        dash_duo.find_element("#btn").click()
        dash_duo.wait_for_text_to_equal("#out", text)


def test_cbsc006_array_of_objects(dash_duo):
    app = dash.Dash()
    app.layout = html.Div(
        [html.Button(id="btn"), dcc.Dropdown(id="dd"), html.Div(id="out")]
    )

    @app.callback(Output("dd", "options"), [Input("btn", "n_clicks")])
    def set_options(n):
        return [{"label": "opt{}".format(i), "value": i} for i in range(n or 0)]

    @app.callback(Output("out", "children"), [Input("dd", "options")])
    def set_out(opts):
        print(repr(opts))
        return len(opts)

    dash_duo.start_server(app)

    dash_duo.wait_for_text_to_equal("#out", "0")
    for i in range(5):
        dash_duo.find_element("#btn").click()
        dash_duo.wait_for_text_to_equal("#out", str(i + 1))
        dash_duo.select_dcc_dropdown("#dd", "opt{}".format(i))


@pytest.mark.parametrize("refresh", [False, True])
def test_cbsc007_parallel_updates(refresh, dash_duo):
    # This is a funny case, that seems to mostly happen with dcc.Location
    # but in principle could happen in other cases too:
    # A callback chain (in this case the initial hydration) is set to update a
    # value, but after that callback is queued and before it returns, that value
    # is also set explicitly from the front end (in this case Location.pathname,
    # which gets set in its componentDidMount during the render process, and
    # callbacks are delayed until after rendering is finished because of the
    # async table)
    # At one point in the wildcard PR #1103, changing from requestQueue to
    # pendingCallbacks, calling PreventUpdate in the callback would also skip
    # any callbacks that depend on pathname, despite the new front-end-provided
    # value.

    app = dash.Dash()

    app.layout = html.Div(
        [
            dcc.Location(id="loc", refresh=refresh),
            html.Button("Update path", id="btn"),
            dash_table.DataTable(id="t", columns=[{"name": "a", "id": "a"}]),
            html.Div(id="out"),
        ]
    )

    @app.callback(Output("t", "data"), [Input("loc", "pathname")])
    def set_data(path):
        return [{"a": (path or repr(path)) + ":a"}]

    @app.callback(
        Output("out", "children"), [Input("loc", "pathname"), Input("t", "data")]
    )
    def set_out(path, data):
        return json.dumps(data) + " - " + (path or repr(path))

    @app.callback(Output("loc", "pathname"), [Input("btn", "n_clicks")])
    def set_path(n):
        if not n:
            raise PreventUpdate

        return "/{0}".format(n)

    dash_duo.start_server(app)

    dash_duo.wait_for_text_to_equal("#out", '[{"a": "/:a"}] - /')
    dash_duo.find_element("#btn").click()
    # the refresh=True case here is testing that we really do get the right
    # pathname, not the prevented default value from the layout.
    dash_duo.wait_for_text_to_equal("#out", '[{"a": "/1:a"}] - /1')
    if not refresh:
        dash_duo.find_element("#btn").click()
        dash_duo.wait_for_text_to_equal("#out", '[{"a": "/2:a"}] - /2')


def test_cbsc008_wildcard_prop_callbacks(dash_duo):
    lock = Lock()

    app = dash.Dash(__name__)
    app.layout = html.Div(
        [
            dcc.Input(id="input", value="initial value"),
            html.Div(
                html.Div(
                    [
                        1.5,
                        None,
                        "string",
                        html.Div(
                            id="output-1",
                            **{"data-cb": "initial value", "aria-cb": "initial value"}
                        ),
                    ]
                )
            ),
        ]
    )

    input_call_count = Value("i", 0)

    @app.callback(Output("output-1", "data-cb"), [Input("input", "value")])
    def update_data(value):
        with lock:
            input_call_count.value += 1
            return value

    @app.callback(Output("output-1", "children"), [Input("output-1", "data-cb")])
    def update_text(data):
        return data

    dash_duo.start_server(app)
    dash_duo.wait_for_text_to_equal("#output-1", "initial value")
    dash_duo.percy_snapshot(name="wildcard-callback-1")

    input1 = dash_duo.find_element("#input")
    dash_duo.clear_input(input1)

    for key in "hello world":
        with lock:
            input1.send_keys(key)

    dash_duo.wait_for_text_to_equal("#output-1", "hello world")
    dash_duo.percy_snapshot(name="wildcard-callback-2")

    # an initial call, one for clearing the input
    # and one for each hello world character
    assert input_call_count.value == 2 + len("hello world")

    assert not dash_duo.get_logs()


def test_cbsc009_callback_using_unloaded_async_component_and_graph(dash_duo):
    app = dash.Dash(__name__)
    app.layout = FragmentComponent(
        [
            CollapseComponent([AsyncComponent(id="async", value="A")]),
            html.Button("n", id="n"),
            DelayedEventComponent(id="d"),
            html.Div("Output init", id="output"),
        ]
    )

    @app.callback(
        Output("output", "children"),
        Input("n", "n_clicks"),
        Input("d", "n_clicks"),
        Input("async", "value"),
    )
    def content(n, d, v):
        return json.dumps([n, d, v])

    dash_duo.start_server(app)

    wait.until(lambda: dash_duo.find_element("#output").text == '[null, null, "A"]', 3)
    dash_duo.wait_for_element("#d").click()

    wait.until(
        lambda: dash_duo.find_element("#output").text == '[null, 1, "A"]', 3,
    )

    dash_duo.wait_for_element("#n").click()
    wait.until(
        lambda: dash_duo.find_element("#output").text == '[1, 1, "A"]', 3,
    )

    dash_duo.wait_for_element("#d").click()
    wait.until(
        lambda: dash_duo.find_element("#output").text == '[1, 2, "A"]', 3,
    )


def test_cbsc010_event_properties(dash_duo):
    app = dash.Dash(__name__)
    app.layout = html.Div([html.Button("Click Me", id="button"), html.Div(id="output")])

    call_count = Value("i", 0)

    @app.callback(Output("output", "children"), [Input("button", "n_clicks")])
    def update_output(n_clicks):
        if not n_clicks:
            raise PreventUpdate
        call_count.value += 1
        return "Click"

    dash_duo.start_server(app)
    dash_duo.wait_for_text_to_equal("#output", "")
    assert call_count.value == 0

    dash_duo.find_element("#button").click()
    dash_duo.wait_for_text_to_equal("#output", "Click")
    assert call_count.value == 1


def test_cbsc011_one_call_for_multiple_outputs_initial(dash_duo):
    app = dash.Dash(__name__)
    call_count = Value("i", 0)

    app.layout = html.Div(
        [
            html.Div(
                [
                    dcc.Input(value="Input {}".format(i), id="input-{}".format(i))
                    for i in range(10)
                ]
            ),
            html.Div(id="container"),
            dcc.RadioItems(),
        ]
    )

    @app.callback(
        Output("container", "children"),
        [Input("input-{}".format(i), "value") for i in range(10)],
    )
    def dynamic_output(*args):
        call_count.value += 1
        return json.dumps(args, indent=2)

    dash_duo.start_server(app)
    dash_duo.wait_for_text_to_equal("#input-9", "Input 9")

    assert call_count.value == 1
    dash_duo.percy_snapshot("test_rendering_layout_calls_callback_once_per_output")


def test_cbsc012_one_call_for_multiple_outputs_update(dash_duo):
    app = dash.Dash(__name__, suppress_callback_exceptions=True)
    call_count = Value("i", 0)

    app.layout = html.Div(
        [
            html.Button(id="display-content", children="Display Content"),
            html.Div(id="container"),
            dcc.RadioItems(),
        ]
    )

    @app.callback(Output("container", "children"), Input("display-content", "n_clicks"))
    def display_output(n_clicks):
        if not n_clicks:
            return ""
        return html.Div(
            [
                html.Div(
                    [
                        dcc.Input(value="Input {}".format(i), id="input-{}".format(i))
                        for i in range(10)
                    ]
                ),
                html.Div(id="dynamic-output"),
            ]
        )

    @app.callback(
        Output("dynamic-output", "children"),
        [Input("input-{}".format(i), "value") for i in range(10)],
    )
    def dynamic_output(*args):
        call_count.value += 1
        return json.dumps(args, indent=2)

    dash_duo.start_server(app)

    dash_duo.find_element("#display-content").click()

    dash_duo.wait_for_text_to_equal("#input-9", "Input 9")
    assert call_count.value == 1

    dash_duo.percy_snapshot("test_rendering_new_content_calls_callback_once_per_output")


def test_cbsc013_multi_output_out_of_order(dash_duo):
    app = dash.Dash(__name__)
    app.layout = html.Div(
        [
            html.Button(id="input", n_clicks=0),
            html.Div(id="output1"),
            html.Div(id="output2"),
        ]
    )

    call_count = Value("i", 0)
    lock = Lock()

    @app.callback(
        Output("output1", "children"),
        Output("output2", "children"),
        Input("input", "n_clicks"),
    )
    def update_output(n_clicks):
        call_count.value += 1
        if n_clicks == 1:
            with lock:
                pass
        return n_clicks, n_clicks + 1

    dash_duo.start_server(app)

    button = dash_duo.find_element("#input")
    with lock:
        button.click()
        button.click()

    dash_duo.wait_for_text_to_equal("#output1", "2")
    dash_duo.wait_for_text_to_equal("#output2", "3")
    assert call_count.value == 3
    dash_duo.percy_snapshot(
        "test_callbacks_called_multiple_times_and_out_of_order_multi_output"
    )
    assert dash_duo.driver.execute_script("return !window.store.getState().isLoading;")


def test_cbsc014_multiple_properties_update_at_same_time_on_same_component(dash_duo):
    call_count = Value("i", 0)
    timestamp_1 = Value("d", -5)
    timestamp_2 = Value("d", -5)

    app = dash.Dash(__name__)
    app.layout = html.Div(
        [
            html.Div(id="container"),
            html.Button("Click", id="button-1", n_clicks=0, n_clicks_timestamp=-1),
            html.Button("Click", id="button-2", n_clicks=0, n_clicks_timestamp=-1),
        ]
    )

    @app.callback(
        Output("container", "children"),
        Input("button-1", "n_clicks"),
        Input("button-1", "n_clicks_timestamp"),
        Input("button-2", "n_clicks"),
        Input("button-2", "n_clicks_timestamp"),
    )
    def update_output(n1, t1, n2, t2):
        call_count.value += 1
        timestamp_1.value = t1
        timestamp_2.value = t2
        return "{}, {}".format(n1, n2)

    dash_duo.start_server(app)

    dash_duo.wait_for_text_to_equal("#container", "0, 0")
    assert timestamp_1.value == -1
    assert timestamp_2.value == -1
    assert call_count.value == 1
    dash_duo.percy_snapshot("button initialization 1")

    dash_duo.find_element("#button-1").click()
    dash_duo.wait_for_text_to_equal("#container", "1, 0")
    assert timestamp_1.value > ((time.time() - (24 * 60 * 60)) * 1000)
    assert timestamp_2.value == -1
    assert call_count.value == 2
    dash_duo.percy_snapshot("button-1 click")
    prev_timestamp_1 = timestamp_1.value

    dash_duo.find_element("#button-2").click()
    dash_duo.wait_for_text_to_equal("#container", "1, 1")
    assert timestamp_1.value == prev_timestamp_1
    assert timestamp_2.value > ((time.time() - 24 * 60 * 60) * 1000)
    assert call_count.value == 3
    dash_duo.percy_snapshot("button-2 click")
    prev_timestamp_2 = timestamp_2.value

    dash_duo.find_element("#button-2").click()
    dash_duo.wait_for_text_to_equal("#container", "1, 2")
    assert timestamp_1.value == prev_timestamp_1
    assert timestamp_2.value > prev_timestamp_2
    assert timestamp_2.value > timestamp_1.value
    assert call_count.value == 4
    dash_duo.percy_snapshot("button-2 click again")


def test_cbsc015_input_output_callback(dash_duo):
    lock = Lock()

    app = dash.Dash(__name__)
    app.layout = html.Div(
        [html.Div("0", id="input-text"), dcc.Input(id="input", type="number", value=0)]
    )

    @app.callback(
        Output("input", "value"), Input("input", "value"),
    )
    def circular_output(v):
        ctx = dash.callback_context
        if not ctx.triggered:
            value = v
        else:
            value = v + 1
        return value

    call_count = Value("i", 0)

    @app.callback(
        Output("input-text", "children"), Input("input", "value"),
    )
    def follower_output(v):
        with lock:
            call_count.value = call_count.value + 1
            return str(v)

    dash_duo.start_server(app)

    input_ = dash_duo.find_element("#input")
    for key in "2":
        with lock:
            input_.send_keys(key)

    wait.until(lambda: dash_duo.find_element("#input-text").text == "3", 2)

    assert call_count.value == 2, "initial + changed once"

    assert not dash_duo.redux_state_is_loading

    assert dash_duo.get_logs() == []
